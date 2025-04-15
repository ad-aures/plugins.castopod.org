<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Entities\Index;
use App\Entities\Plugin;
use App\Entities\Version;
use App\Libraries\PluginRepositoryCrawler;
use App\Models\IndexModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Queue\BaseJob;
use CodeIgniter\Queue\Interfaces\JobInterface;
use Exception;

class CrawlPlugin extends BaseJob implements JobInterface
{
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
        $tempRepoPath = tempdir('plugin-repo_');

        if (! $tempRepoPath) {
            throw new Exception('Could not create temporary repository folder.');
        }

        try {
            $prc = new PluginRepositoryCrawler(
                (string) $pluginIndex->repository_url,
                $pluginIndex->manifest_root,
                $tempRepoPath,
            );
        } catch (Exception $e) {
            delete_directory($tempRepoPath);

            throw $e;
        }

        try {
            $db = db_connect();
            $db->transBegin();

            if ($prc->pluginMetadata['private'] ?? false) {
                // plugin is private, remove from index and stop
                new IndexModel()
                    ->delete($pluginIndex->id);

                throw new Exception('Plugin is private.');
            }

            // TODO: check if is official via list of official repositories
            $isOfficial = $prc->pluginMetadata['vendor'] === 'ad-aures'; // official plugins are published by ad-aures

            $keywords = $prc->pluginMetadata['keywords'];
            if ($isOfficial) {
                $keywords = ['official', ...$keywords];
            }

            $newPlugin = new Plugin([
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

            if (! new PluginModel()->insert($newPlugin, false)) {
                throw new Exception('Error when inserting plugin ' . $newPlugin->key);
            }

            $versionModel = new VersionModel();
            foreach ($prc->versions as $tag => $data) {
                $prc->switchVersion($data['refname']);

                // check that manifest version and tag are the same, don't register version otherwise
                if (! $data['isDev'] && $prc->pluginMetadata['version'] !== $tag) {
                    continue;
                }

                $newVersion = new Version([
                    'plugin_key'           => $prc->pluginMetadata['key'],
                    'tag'                  => $tag,
                    'commit_hash'          => $data['commitHash'],
                    'readme_markdown'      => $prc->pluginMetadata['readme'],
                    'license'              => $prc->pluginMetadata['license'],
                    'min_castopod_version' => $prc->pluginMetadata['minCastopodVersion'],
                    'hooks'                => $prc->pluginMetadata['hooks'],
                    'size'                 => $prc->pluginMetadata['size'],
                    'file_count'           => $prc->pluginMetadata['fileCount'],
                    'published_at'         => $data['publishedAt'],
                ]);

                if (! $versionModel->insert($newVersion)) {
                    throw new Exception(sprintf(
                        'Error when inserting version %s: %s',
                        $tag,
                        print_r($versionModel->errors(), true),
                    ));
                }
            }

            // FIXME: casts don't work with batch insert
            // if ($versionModel->insertBatch($versions) === false) {
            //     throw new Exception('Error when inserting versions: ' . print_r($versionModel->errors(), true));
            // }

            // done - Success!
            $db->transComplete();

            CLI::write(sprintf('Successfully crawled plugin "%s"', $newPlugin->key));
        } catch (Exception $e) {
            $db->transRollback();
            throw $e;
        } finally {
            delete_directory($tempRepoPath);
        }
    }
}
