<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Entities\Enums\Category;
use App\Entities\Enums\Hook;
use App\Libraries\PluginRepositoryCrawler;
use App\Models\IndexModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;
use Exception;

class UpdatePlugin extends BaseJob implements JobInterface
{
    public function process(): void
    {
        if (! array_key_exists('plugin_key', $this->data)) {
            throw new Exception('"plugin_key" is missing from data.');
        }

        $plugin = new PluginModel()
            ->getPluginByKey($this->data['plugin_key']);

        // create temp folder where repo is to be cloned
        $tempRepoPath = tempdir('plugin-repo_');

        if (! $tempRepoPath) {
            throw new Exception('Could not create temporary repository folder.');
        }
            
        try {
            $prc = new PluginRepositoryCrawler((string) $plugin->repository_url, $plugin->manifest_root, $tempRepoPath);
        } catch (Exception $e) {
            delete_directory($tempRepoPath);

            new PluginModel()
                ->setUpdating($plugin->id, false);

            throw $e;
        }

        try {
            $db = db_connect();
            $db->transBegin();

            if ($prc->pluginMetadata['private']) {
                // plugin is private, remove from index and stop
                new IndexModel()
                    ->where([
                        'repository_url' => $plugin->repository_url,
                        'manifest_root'  => $plugin->manifest_root,
                    ])
                    ->delete();

                if (! new IndexModel()->deletePluginFromIndex($plugin)) {
                    throw new Exception("Plugin is private but couldn't be removed from the index.");
                }

                throw new Exception('Plugin is private. It has been removed from the index.');
            }

            // TODO: check if is official via list of official repositories
            $isOfficial = $prc->pluginMetadata['vendor'] === 'ad-aures'; // official plugins are published by ad-aures

            $keywords = $prc->pluginMetadata['keywords'];
            if ($isOfficial) {
                $keywords = ['official', ...$keywords];
            }

            $plugin->description = $prc->pluginMetadata['description'];

            $plugin->icon_svg = $prc->pluginMetadata['icon'];

            $plugin->homepage_url = $prc->pluginMetadata['homepage'] === null ? null : new URI(
                $prc->pluginMetadata['homepage'],
            );

            $plugin->categories = Category::getFromArray($keywords);

            $plugin->authors = $prc->pluginMetadata['authors'];

            if (! new PluginModel()->save($plugin)) {
                throw new Exception('Error when updating plugin: ' . $plugin->key);
            }

            $versionModel = new VersionModel();
            $upsertedVersionTags = [];
            foreach ($prc->versions as $tag => $data) {
                $prc->switchVersion($data['refname']);

                // do not register version if the keys are different
                if ($prc->pluginMetadata['key'] !== $plugin->key) {
                    continue;
                }

                // check that manifest version and tag are the same, don't register version otherwise
                if (! $data['isDev'] && $prc->pluginMetadata['version'] !== $tag) {
                    continue;
                }

                $hooks = array_map(
                    fn (Hook $enum) => $enum->value,
                    Hook::getFromArray($prc->pluginMetadata['hooks']),
                );

                $query = $versionModel->builder()
                    ->onConstraint(['plugin_key', 'tag'])
                    ->updateFields(
                        [
                            'commit_hash',
                            'readme_markdown',
                            'license',
                            'min_castopod_version',
                            'hooks',
                            'size',
                            'file_count',
                            'published_at',
                        ],
                    )->setData([
                        'plugin_key'           => $plugin->key,
                        'tag'                  => $tag,
                        'commit_hash'          => $data['commitHash'],
                        'readme_markdown'      => $prc->pluginMetadata['readme'],
                        'license'              => $prc->pluginMetadata['license'],
                        'min_castopod_version' => $prc->pluginMetadata['minCastopodVersion'],
                        'hooks'                => sprintf('{%s}', implode(',', $hooks)),
                        'size'                 => $prc->pluginMetadata['size'],
                        'file_count'           => $prc->pluginMetadata['fileCount'],
                        'published_at'         => $data['publishedAt'],
                    ]);

                assert($query instanceof BaseBuilder);

                $upsertResult = $query->upsert();

                if ($upsertResult === false) {
                    throw new Exception('Error when upserting version: ' . $tag);
                }

                $upsertedVersionTags[] = $tag;
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

            CLI::write(sprintf('Successfully updated plugin "%s"', $plugin->key));
        } catch (Exception $e) {
            $db->transRollback();
            throw $e;
        } finally {
            delete_directory($tempRepoPath);

            new PluginModel()
                ->setUpdating($plugin->id, false);
        }
    }
}
