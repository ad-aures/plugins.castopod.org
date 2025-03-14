<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Entities\Index;
use App\Entities\Plugin;
use App\Entities\Version;
use App\Models\IndexModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;
use CodeIgniter\Validation\Validation;
use enshrined\svgSanitize\Sanitizer;
use Exception;

class CrawlPlugin extends BaseJob implements JobInterface
{
    protected string $tempRepoPath = '';

    protected string $manifestFilePath = '';

    protected string $readmeFilePath = '';

    protected string $licenseFilePath = '';

    protected string $iconFilePath = '';

    public function process(): void
    {
        if (! array_key_exists('index_id', $this->data)) {
            throw new Exception('"index_id" is missing from data.');
        }

        if (! is_int($this->data['index_id'])) {
            throw new Exception('"index_id" is not a number.');
        }

        $pluginIndex = new IndexModel()
            ->find($this->data['index_id']);

        if (! $pluginIndex instanceof Index) {
            throw new Exception('Could not get plugin from the index.');
        }

        // create temp folder where repo is to be cloned
        $tempRepoPath = $this->tempdir(WRITEPATH . 'temp', 'plugin-repo_');

        if (! $tempRepoPath) {
            throw new Exception('Could not create temporary repository folder.');
        }

        $this->tempRepoPath = $tempRepoPath;

        $pluginFolder = implode(
            DIRECTORY_SEPARATOR,
            [$this->tempRepoPath, $pluginIndex->repository_folder],
        ) . DIRECTORY_SEPARATOR;
        $this->manifestFilePath = $pluginFolder . 'manifest.json';
        $this->readmeFilePath = $pluginFolder . 'README.md';
        $this->licenseFilePath = $pluginFolder . 'LICENSE.md';
        $this->iconFilePath = $pluginFolder . 'icon.svg';

        try {
            $db = db_connect();

            $db->transBegin();

            // clone repository
            exec(sprintf('cd %s && git init', $this->tempRepoPath));
            exec(sprintf('cd %s && git remote add -f origin %s', $this->tempRepoPath, $pluginIndex->repository_url));

            // do a sparse checkout when plugin is in subfolder
            if ($pluginIndex->repository_folder !== '') {
                exec(sprintf('cd %s && git config core.sparseCheckout true', $this->tempRepoPath));
                exec(
                    sprintf(
                        'cd %s && echo "%s" >> .git/info/sparse-checkout',
                        $this->tempRepoPath,
                        $pluginIndex->repository_folder,
                    ),
                );
            }

            // get default branch
            $defaultBranch = shell_exec(sprintf('cd %s && git branch --show-current', $this->tempRepoPath));

            if (! $defaultBranch) {
                throw new Exception('Could not get default branch.');
            }

            // clean default branch output
            $defaultBranch = trim($defaultBranch);

            // pull from origin / default branch
            exec(sprintf('cd %s && git pull origin %s', $this->tempRepoPath, $defaultBranch));

            $manifestData = $this->parseManifest();

            if ($manifestData['private'] ?? false) {
                // plugin is private, remove from index and stop
                new IndexModel()
                    ->delete($pluginIndex->id);

                throw new Exception('Plugin is private.');
            }

            [$vendor, $name] = explode('/', $manifestData['name']);

            $dirtyIcon = (string) @file_get_contents($this->iconFilePath);

            $cleanIcon = (string) new Sanitizer()
                ->sanitize($dirtyIcon);

            $newPlugin = new Plugin([
                'vendor'            => $vendor,
                'name'              => $name,
                'description'       => $manifestData['description'] ?? '',
                'icon_svg'          => $cleanIcon,
                'repository_url'    => $pluginIndex->repository_url,
                'repository_folder' => $pluginIndex->repository_folder,
                'homepage'          => $manifestData['homepage'] ?? '',
                'categories'        => $manifestData['keywords'] ?? [],
            ]);

            $newPluginId = new PluginModel()
                ->insert($newPlugin);

            assert(is_int($newPluginId));

            /**
             * CRAWL VERSIONS
             */

            $lastCommitInfo = shell_exec(sprintf('cd %s && git log -1 --format="%%H%%x09%%aI"', $this->tempRepoPath));

            if (! $lastCommitInfo) {
                throw new Exception('Could not get last commit info.');
            }

            [$commit, $publicationDate] = explode("\t", trim($lastCommitInfo));
            $devVersion = $this->getVersionData(
                $newPluginId,
                sprintf('dev-%s', $defaultBranch),
                $commit,
                Time::createFromFormat(DATE_ATOM, $publicationDate),
                isDev: true,
            );

            if (! $devVersion) {
                throw new Exception('Could not get dev version data.');
            }

            $versionModel = new VersionModel();
            if (! $versionModel->insert($devVersion)) {
                throw new Exception('Error when inserting dev version: ' . print_r($versionModel->errors(), true));
            }

            // TODO: test this with a test repository

            // get all versions with refs
            $tagsList = shell_exec(
                sprintf(
                    'cd %s && git tag -l "%s" --format="%%(creatordate:iso-strict)%%09%%(objectname)%%09%%(refname:strip=2)" --sort=creatordate',
                    $this->tempRepoPath,
                    ($manifestData['submodule'] ?? false) ? $name . '@*' : '*',
                ),
            );

            $validation = service('validation');

            $tagsList = $tagsList ? explode(PHP_EOL, $tagsList) : [];
            foreach ($tagsList as $tagLine) {
                [$creatorDate, $objectName, $refname] = explode("\t", $tagLine);

                // extract semantic version from refname
                if ($manifestData['submodule'] ?? false) {
                    // handle submodule refname, ie. <package-name>@1.0.0
                    $tag = preg_replace('/^' . preg_quote($manifestData['name'], '/') . '@/', '', $refname);
                } else {
                    // remove 'v' if present
                    $tag = preg_replace('/^v/', '', $refname);
                }

                if ($tag === null) {
                    throw new Exception('Error when removing "v" from tag.');
                }

                // check that refname is a version
                if (! $validation->check(
                    $tag,
                    'regex_match[/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/]',
                )) {
                    continue;
                }

                // switch temp git repo to tag
                exec(sprintf('cd %s && git switch --detach %s', $this->tempRepoPath, $refname));

                $newVersion = $this->getVersionData(
                    $newPluginId,
                    $tag,
                    $objectName,
                    Time::createFromFormat(DATE_ATOM, $creatorDate),
                );

                if (! $newVersion) {
                    continue;
                }

                if (! $versionModel->insert($newVersion)) {
                    throw new Exception('Error when inserting dev version: ' . print_r($versionModel->errors(), true));
                }
            }

            // FIXME: casts don't work with batch insert
            // if ($versionModel->insertBatch($versions) === false) {
            //     throw new Exception('Error when inserting versions: ' . print_r($versionModel->errors(), true));
            // }

            // done - Success!
            $db->transComplete();
            echo 'Successfully crawled plugin!';
        } catch (Exception $e) {
            $db->transRollback();
            throw $e;
        } finally {
            // delete repo in temp folder
            helper('file');

            delete_files($this->tempRepoPath, true, false, true);
            rmdir($this->tempRepoPath);
        }
    }

    /**
     * @return array{name:string,minCastopodVersion:string,hooks:list<string>,version:string,description?:string,authors?:array{name:string,email?:string,url?:string},homepage?:string,license?:string,private?:bool,submodule?:bool,keywords?:list<string>}
     */
    private function parseManifest(): array
    {
        // check that manifest.json exists
        if (! file_exists($this->manifestFilePath)) {
            throw new Exception('manifest.json was not found!');
        }

        $manifestContents = file_get_contents($this->manifestFilePath);

        if (! $manifestContents) {
            throw new Exception('manifest.json file could not be read.');
        }

        if (! json_validate($manifestContents)) {
            throw new Exception('manifest.json file is not a valid json.');
        }

        $manifestData = json_decode($manifestContents, true);

        if (! is_array($manifestData)) {
            throw new Exception('Error when decoding the manifest data.');
        }

        $validationRules = [
            'name'               => 'required|max_length[128]|regex_match[/^[a-z0-9]([_.-]?[a-z0-9]+)*\/[a-z0-9]([_.-]?[a-z0-9]+)*$/]',
            'minCastopodVersion' => 'required|regex_match[/^(0|[1-9]\d*)\.(0|[1-9]\d*)(\.(0|[1-9]\d*))?(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/]',
            'hooks.*'            => 'required|in_list[rssBeforeChannel,rssAfterChannel,rssBeforeItem,rssAfterItem,siteHead]',
            'version'            => 'required|regex_match[/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/]',
            'description'        => 'permit_empty|max_length[256]',
            'authors'            => 'permit_empty|is_list',
            'homepage'           => 'permit_empty|valid_url_strict',
            'license'            => 'permit_empty|string',
            'private'            => 'permit_empty|is_boolean',
            'submodule'          => 'permit_empty|is_boolean',
            'keywords.*'         => 'permit_empty',
        ];

        /** @var Validation $validation */
        $validation = service('validation');

        $validation->setRules($validationRules);

        if (! $validation->run($manifestData)) {
            throw new Exception('manifest.json file has errors: ' . print_r($validation->getErrors(), true));
        }

        /** @var array{name:string,minCastopodVersion:string,hooks:list<string>,version:string,description?:string,authors?:array{name:string,email?:string,url?:string},homepage?:string,license?:string,private?:bool,submodule?:bool,keywords?:list<string>} $validatedData */
        $validatedData = $validation->getValidated();

        return $validatedData;
    }

    /**
     * @return Version|false new version or false on failure
     */
    private function getVersionData(
        int $pluginId,
        string $tag,
        string $commit,
        Time $publishedAt,
        bool $isDev = false,
    ): Version|false {
        $manifestData = $this->parseManifest();

        // make sure that version and tag are the same, fail otherwise
        if (! $isDev && $manifestData['version'] !== $tag) {
            return false;
        }

        // get readme and license contents if present
        $readmeMarkdown = (string) @file_get_contents($this->readmeFilePath);
        $licenseMarkdown = (string) @file_get_contents($this->licenseFilePath);

        return new Version([
            'plugin_id'            => $pluginId,
            'tag'                  => $tag,
            'commit'               => $commit,
            'readme_markdown'      => $readmeMarkdown,
            'license'              => $manifestData['license'] ?? '',
            'license_markdown'     => $licenseMarkdown,
            'min_castopod_version' => $manifestData['minCastopodVersion'],
            'hooks'                => $manifestData['hooks'],
            'published_at'         => $publishedAt,
        ]);
    }

    /**
     * Creates a random unique temporary directory, with specified parameters,
     * that does not already exist (like tempnam(), but for dirs).
     *
     * Created dir will begin with the specified prefix, followed by random
     * numbers.
     *
     * @link https://php.net/manual/en/function.tempnam.php
     * @link adapted from https://stackoverflow.com/a/30010928
     *
     * @param string|null $dir Base directory under which to create temp dir.
     *     If null, the default system temp dir (sys_get_temp_dir()) will be
     *     used.
     * @param string $prefix String with which to prefix created dirs.
     * @param int $mode Octal file permission mask for the newly-created dir.
     *     Should begin with a 0.
     * @param int $maxAttempts Maximum attempts before giving up (to prevent
     *     endless loops).
     * @return string|false Full path to newly-created dir, or false on failure.
     */
    private function tempdir(
        ?string $dir = null,
        string $prefix = 'tmp_',
        int $mode = 0700,
        int $maxAttempts = 1000,
    ): string|false {
        /* Use the system temp dir by default. */
        if ($dir === null) {
            $dir = sys_get_temp_dir();
        }

        /* Trim trailing slashes from $dir. */
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        /* If we don't have permission to create a directory, fail, otherwise we will
         * be stuck in an endless loop.
         */
        if (! is_dir($dir) || ! is_writable($dir)) {
            return false;
        }

        /* Make sure characters in prefix are safe. */
        if (strpbrk($prefix, '\\/:*?"<>|') !== false) {
            return false;
        }

        /* Attempt to create a random directory until it works. Abort if we reach
         * $maxAttempts. Something screwy could be happening with the filesystem
         * and our loop could otherwise become endless.
         */
        $attempts = 0;
        do {
            $path = sprintf('%s%s%s%s', $dir, DIRECTORY_SEPARATOR, $prefix, mt_rand(100000, mt_getrandmax()));
        } while (
            ! mkdir($path, $mode) &&
            $attempts++ < $maxAttempts
        );

        return $path;
    }
}
