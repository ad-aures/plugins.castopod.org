<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Entities\Install;
use App\Entities\Plugin;
use App\Models\DownloadModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Exception;

class FakeInstallsSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Plugin[] $allPlugins */
        $allPlugins = new PluginModel()
            ->findAll();

        if ($allPlugins === []) {
            throw new Exception('No plugins found, nothing to populate.');
        }

        $pluginModel = new PluginModel();
        $versionModel = new VersionModel();
        $downloadModel = new DownloadModel();
        foreach ($allPlugins as $plugin) {
            $data = [];
            $pluginDownloadsTotal = 0;
            foreach ($plugin->versions as $version) {
                $versionDownloadsTotal = 0;
                for ($i = 0; $i < 300; $i++) {
                    $count = random_int(0, 50);
                    $data[] = new Install([
                        'version_id' => $version->id,
                        'date'       => Time::now()->subDays($i),
                        'count'      => $count,
                    ]);
                    $pluginDownloadsTotal += $count;
                    $versionDownloadsTotal += $count;
                }

                $version->installs_total = $versionDownloadsTotal;
                $versionModel->save($version);
            }

            $plugin->installs_total = $pluginDownloadsTotal;
            $pluginModel->save($plugin);

            $downloadModel->insertBatch($data);
        }
    }
}
