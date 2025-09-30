<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Entities\Enums\Category;
use App\Entities\Enums\Hook;
use App\Entities\Enums\License;
use App\Entities\Index;
use App\Entities\Plugin;
use App\Entities\Version;
use App\Exceptions\PluginNotFoundException;
use App\Libraries\Markdown;
use App\Libraries\PluginRepositoryCrawler;
use App\Models\IndexModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;
use Exception;

class CrawlPlugin extends BaseJob implements JobInterface
{
    public function process(): void
    {
        try {
            $db = db_connect();
            $db->transBegin();

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
            $tempRepoPath = tempdir('plugin-repo_');

            if (! $tempRepoPath) {
                throw new Exception('Could not create temporary repository folder.');
            }

            $prc = new PluginRepositoryCrawler(
                (string) $pluginIndex->repository_url,
                $pluginIndex->manifest_root,
                $tempRepoPath,
            );

            if ($prc->pluginMetadata['private'] ?? false) {
                // plugin is private, remove from index and stop
                new IndexModel()
                    ->delete($pluginIndex->id);

                throw new Exception('Plugin is private. It has been removed from the index.');
            }

            $officialRepositoriesTxtPath = ROOTPATH . 'official-repositories.txt';
            $officialReposList = [];
            if (file_exists($officialRepositoriesTxtPath)) {
                // load official-repositories.txt file
                $officialRepos = (string) file_get_contents($officialRepositoriesTxtPath);
                $officialReposList = preg_split("/\r\n|\n|\r/", $officialRepos);
                if (! $officialReposList) {
                    $officialReposList = [];
                }
            }

            // check if is official via list of official repositories
            $isOfficial = in_array((string) $pluginIndex->repository_url, $officialReposList, true);

            $keywords = $prc->pluginMetadata['keywords'];
            if ($isOfficial) {
                $keywords = ['official', ...$keywords];
            }

            $pluginModel = new PluginModel();
            try {
                $plugin = $pluginModel
                    ->getPluginByRepository((string) $pluginIndex->repository_url, $pluginIndex->manifest_root);

                $plugin->description = $prc->pluginMetadata['description'];
                $plugin->icon_svg = $prc->pluginMetadata['icon'];
                $plugin->homepage_url = $prc->pluginMetadata['homepage'] === null ? null : new URI(
                    $prc->pluginMetadata['homepage'],
                );
                $plugin->categories = Category::getFromArray($keywords);
                $plugin->authors = $prc->pluginMetadata['authors'];

                if ($plugin->hasChanged() && ! $pluginModel->save($plugin)) {
                    throw new Exception(sprintf('Error when updating plugin: %s', $plugin->key));
                }
            } catch (PluginNotFoundException) {
                $plugin = new Plugin([
                    'key'            => $prc->pluginMetadata['key'],
                    'description'    => $prc->pluginMetadata['description'],
                    'icon_svg'       => $prc->pluginMetadata['icon'],
                    'repository_url' => $pluginIndex->repository_url,
                    'manifest_root'  => $pluginIndex->manifest_root,
                    'homepage_url'   => $prc->pluginMetadata['homepage'],
                    'categories'     => $keywords,
                    'authors'        => $prc->pluginMetadata['authors'],
                    'owner_id'       => $pluginIndex->submitted_by,
                ]);

                if (! $pluginModel->insert($plugin, false)) {
                    throw new Exception('Error when inserting plugin ' . $plugin->key);
                }
            }

            $versionModel = new VersionModel();
            $upsertedVersionTags = [];
            foreach ($prc->versions as $versionData) {
                $prc->switchVersion($versionData['refname']);

                if ($prc->pluginMetadata['size'] > config('App')->maxPackageSize) {
                    // plugin package size is more than defined size limit, discard this version
                    continue;
                }

                // check that manifest version and tag are the same, don't register version otherwise
                if (! $versionData['isDev'] && $prc->pluginMetadata['version'] !== $versionData['tag']) {
                    continue;
                }

                try {
                    $version = $versionModel->getPluginVersion($plugin->key, $versionData['tag']);

                    if ($prc->pluginMetadata['checksum'] !== $version->archive_checksum) {
                        // contents differ when comparing checksum
                        // make sure to delete old archive
                        delete_path(media_path('plugins', $version->archive_path), media_path('plugins'));

                        // create new archive
                        $archivePath = $prc->archive($plugin->key, $version->tag);

                        $version->archive_path = $archivePath;
                        $version->archive_checksum = $prc->pluginMetadata['checksum'];
                    }

                    $version->commit_hash = $versionData['commitHash'];
                    $version->readme_markdown = new Markdown($prc->pluginMetadata['readme']);
                    $version->license = License::getFrom($prc->pluginMetadata['license']);
                    $version->min_castopod_version = $prc->pluginMetadata['minCastopodVersion'];
                    $version->hooks = Hook::getFromArray($prc->pluginMetadata['hooks']);
                    $version->size = $prc->pluginMetadata['size'];
                    $version->file_count = $prc->pluginMetadata['fileCount'];
                    $version->published_at = $versionData['publishedAt'];

                    if ($version->hasChanged() && ! $versionModel->save($version)) {
                        throw new Exception(sprintf(
                            'Error when updating version "%s" of plugin: %s',
                            $versionData['tag'],
                            $plugin->key,
                        ));
                    }
                } catch (PluginNotFoundException) {
                    $version = new Version([
                        'plugin_key'           => $prc->pluginMetadata['key'],
                        'tag'                  => $versionData['tag'],
                        'commit_hash'          => $versionData['commitHash'],
                        'readme_markdown'      => $prc->pluginMetadata['readme'],
                        'license'              => $prc->pluginMetadata['license'],
                        'min_castopod_version' => $prc->pluginMetadata['minCastopodVersion'],
                        'hooks'                => $prc->pluginMetadata['hooks'],
                        'size'                 => $prc->pluginMetadata['size'],
                        'file_count'           => $prc->pluginMetadata['fileCount'],
                        'archive_path'         => $prc->archive($plugin->key, $versionData['tag']),
                        'archive_checksum'     => $prc->pluginMetadata['checksum'],
                        'published_at'         => $versionData['publishedAt'],
                    ]);

                    if (! $versionModel->insert($version)) {
                        throw new Exception(sprintf(
                            'Error when inserting version %s: %s',
                            $versionData['tag'],
                            print_r($versionModel->errors(), true),
                        ));
                    }
                }

                $upsertedVersionTags[] = $versionData['tag'];
            }

            if ($upsertedVersionTags === []) {
                // no version was upserted, fail
                throw new Exception(sprintf('No version matched for plugin "%s"', $plugin->key));
            }

            // delete versions not upserted during crawl
            new VersionModel()
                ->where('plugin_key', $plugin->key)
                ->whereNotIn('tag', $upsertedVersionTags)
                ->delete();

            // done - Success!
            $db->transComplete();

            CLI::write(sprintf('Successfully crawled plugin "%s"', $plugin->key));
        } catch (Exception $e) {
            $db->transRollback();

            throw $e;
        } finally {
            if (isset($tempRepoPath) && $tempRepoPath !== false) {
                delete_directory($tempRepoPath);
            }

            if (isset($plugin)) {
                // set updating flag to false at the end even if it's only required when updating
                new PluginModel()
                    ->setUpdating($plugin->id, false);
            }
        }
    }
}
