<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Entities\Enums\Hook;
use App\Entities\Person;
use CodeIgniter\I18n\Time;
use CodeIgniter\Validation\Validation;
use Config\App;
use enshrined\svgSanitize\Sanitizer;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PluginRepositoryCrawler
{
    /**
     * @var array{key:string,vendor:string,name:string,version:string,minCastopodVersion:string,hooks:list<string>,description:?string,homepage:?string,authors:list<Person>,license:string,keywords:list<string>,size:int,fileCount:int,icon:string,private:bool,submodule:bool,readme:string}
     */
    public private(set) array $pluginMetadata;

    /**
     * @var array<string,array{refname:string,commitHash:string,publishedAt:Time,isDev:boolean}>
     */
    public private(set) array $versions = [];

    protected string $pluginFolderPath;

    protected string $manifestFilePath;

    protected string $readmeFilePath;

    protected string $iconFilePath;

    protected string $pluginKey;

    public function __construct(
        protected string $url,
        protected string $subfolder,
        protected string $tempRepoPath,
    ) {
        $this->pluginFolderPath = implode(
            DIRECTORY_SEPARATOR,
            [$this->tempRepoPath, $this->subfolder],
        ) . DIRECTORY_SEPARATOR;
        $this->manifestFilePath = $this->pluginFolderPath . 'manifest.json';
        $this->readmeFilePath = $this->pluginFolderPath . 'README.md';
        $this->iconFilePath = $this->pluginFolderPath . 'icon.svg';

        // clone repository
        exec(sprintf('cd %s && git init', $this->tempRepoPath));
        exec(sprintf('cd %s && git remote add -f origin %s', $this->tempRepoPath, $this->url));

        // do a sparse checkout to get subfolder
        if ($this->subfolder !== '') {
            exec(sprintf('cd %s && git config core.sparseCheckout true', $this->tempRepoPath));
            exec(
                sprintf('cd %s && echo "%s" >> .git/info/sparse-checkout', $this->tempRepoPath, $this->subfolder),
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

        $lastCommitInfo = shell_exec(sprintf('cd %s && git log -1 --format="%%H%%x09%%aI"', $this->tempRepoPath));

        if (! $lastCommitInfo) {
            throw new Exception('Could not get last commit info.');
        }

        [$commitHash, $publicationDate] = explode("\t", trim($lastCommitInfo));
        $this->versions[sprintf('dev-%s', $defaultBranch)] = [
            'refname'     => $defaultBranch,
            'commitHash'  => $commitHash,
            'publishedAt' => Time::createFromFormat(DATE_ATOM, $publicationDate),
            'isDev'       => true,
        ];

        $this->pluginMetadata = $this->parseManifest();

        // get all versions with refs
        $tagsList = shell_exec(
            sprintf(
                'cd %s && git tag -l "%s" --format="%%(creatordate:iso-strict)%%09%%(objectname)%%09%%(refname:strip=2)" --sort=creatordate',
                $this->tempRepoPath,
                $this->pluginMetadata['submodule'] ? '*' . $this->pluginMetadata['key'] . '@*' : '*',
            ),
        );

        $validation = service('validation');

        $tagsList = $tagsList ? explode(PHP_EOL, $tagsList) : [];
        foreach ($tagsList as $tagLine) {
            [$creatorDate, $objectName, $refname] = explode("\t", $tagLine);

            // extract semantic version from refname
            if ($this->pluginMetadata['submodule']) {
                // handle submodule refname, ie. remove "<package-name>@" prefix <package-name>@1.0.0 or @<package-name>@1.0.0
                $tag = preg_replace('/^@?' . preg_quote($this->pluginMetadata['key'], '/') . '@/', '', $refname);
            } else {
                // remove 'v' if present
                $tag = preg_replace('/^v/', '', $refname);
            }

            if ($tag === null) {
                throw new Exception('Error when using preg_replace on tag.');
            }

            // check that refname is a proper semantic version
            if (! $validation->check(
                $tag,
                'regex_match[/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/]',
            )) {
                continue;
            }

            $this->versions[$tag] = [
                'refname'     => $refname,
                'commitHash'  => $objectName,
                'publishedAt' => Time::createFromFormat(DATE_ATOM, $creatorDate),
                'isDev'       => false,
            ];
        }
    }

    public function switchVersion(string $tag): void
    {
        // switch temp git repo to tag
        exec(sprintf('cd %s && git switch --detach %s', $this->tempRepoPath, $tag));

        $this->pluginMetadata = $this->parseManifest();
    }

    /**
     * @return array{key:string,vendor:string,name:string,version:string,minCastopodVersion:string,hooks:list<string>,description:?string,homepage:?string,authors:list<Person>,license:string,keywords:list<string>,size:int,fileCount:int,icon:string,private:bool,submodule:bool,readme:string}
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
            'version'            => 'required|regex_match[/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/]',
            'minCastopodVersion' => 'required|regex_match[/^(0|[1-9]\d*)\.(0|[1-9]\d*)(\.(0|[1-9]\d*))?(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/]',
            'hooks.*'            => 'required|in_list[' . implode(',', Hook::values()) . ']',
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

        /** @var array{name:string,version:string,minCastopodVersion:string,hooks:list<string>,description?:string,authors?:list<array{name:string,email?:string,url?:string}|string>,homepage?:string,license?:string,private?:bool,submodule?:bool,keywords?:list<string>} $validatedData */
        $validatedData = $validation->getValidated();

        [$vendor, $name] = explode('/', $validatedData['name']);
        [$bytesTotal, $fileCount] = $this->getDirectoryMetadata($this->pluginFolderPath);

        /** @var list<string> $keywords */
        $keywords = array_filter(
            $validatedData['keywords'] ?? [],
            fn (string $keyword) => strtolower($keyword) !== 'official',
        );

        $dirtyIcon = (string) @file_get_contents($this->iconFilePath);
        $cleanIcon = (string) new Sanitizer()
            ->sanitize($dirtyIcon);

        /** @var App $appConfig */
        $appConfig = config('App');

        // check that the icon does not exceed size limit, discard otherwise
        if (strlen($cleanIcon) > $appConfig->maxIconSize) {
            $cleanIcon = '';
        }

        return [
            'key'                => $validatedData['name'],
            'vendor'             => $vendor,
            'name'               => $name,
            'version'            => $validatedData['version'],
            'minCastopodVersion' => $validatedData['minCastopodVersion'],
            'hooks'              => $validatedData['hooks'],
            'description'        => $validatedData['description'] ?? null,
            'homepage'           => $validatedData['homepage'] ?? null,
            'authors'            => $this->parseAuthors($validatedData['authors'] ?? []),
            'license'            => $validatedData['license'] ?? '',
            'keywords'           => $keywords,
            'size'               => $bytesTotal,
            'fileCount'          => $fileCount,
            'icon'               => $cleanIcon,
            'submodule'          => $validatedData['submodule'] ?? false,
            'readme'             => (string) file_get_contents($this->readmeFilePath),
            'private'            => $validatedData['private'] ?? false,
        ];
    }

    /**
     * @param array<array{name:string,email?:string,url?:string}|string> $authors
     * @return list<Person>
     */
    private function parseAuthors(array $authors): array
    {
        /** @var list<Person> $parsedAuthors */
        $parsedAuthors = [];

        foreach ($authors as $author) {
            if (is_string($author)) {
                $result = preg_match(
                    '/^(?<name>[^<>()]*)\s*(<(?<email>.*)>)?\s*(\((?<url>.*)\))?$/',
                    $author,
                    $matches,
                );

                if (! $result) {
                    throw new Exception('Author string is not valid.');
                }

                $newAuthor = [
                    'name' => $matches['name'],
                ];

                if (array_key_exists('email', $matches)) {
                    $newAuthor['email'] = $matches['email'];
                }

                if (array_key_exists('url', $matches)) {
                    $newAuthor['url'] = $matches['url'];
                }

                $author = $newAuthor;
            }

            $validation = service('validation');
            $validation->setRules([
                'name'  => 'required|max_length[64]',
                'email' => 'permit_empty|max_length[254]|valid_email',
                'url'   => 'permit_empty|valid_url_strict',
            ]);

            if ($validation->run($author)) {
                /** @var array{name:string,email?:string,url?:string} */
                $validData = $validation->getValidated();

                $parsedAuthors[] = new Person($validData);
            }
        }

        return $parsedAuthors;
    }

    /**
     * @link from https://stackoverflow.com/a/21409562
     *
     * @return array{0:int,1:int}
     */
    private function getDirectoryMetadata(string $path): array
    {
        $bytesTotal = 0;
        $fileCount = 0;
        $path = (string) realpath($path);
        if ($path !== '' && file_exists($path)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::SKIP_DOTS,
            ));
            /** @var \SplFileObject $object */
            foreach ($files as $object) {
                $bytesTotal += $object->getSize();
                $fileCount++;
            }
        }

        return [$bytesTotal, $fileCount];
    }
}
