<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Entities\Index;
use App\Models\IndexModel;
use CodeIgniter\Database\Seeder;

class OfficialPluginsSeeder extends Seeder
{
    public function run(): void
    {
        $repositoryUrl = 'https://github.com/ad-aures/castopod-plugins.git';

        $pluginsFolders = [
            'custom-head',
            'custom-rss',
            'foo',
            'op3',
            'owner-email-remover',
            'podcast-block',
            'podcast-episode-season',
            'podcast-images',
            'podcast-license',
            'podcast-medium',
            'podcast-podroll',
            'podcast-txt',
            'show-notes-signature',
        ];

        $db = db_connect();
        foreach ($pluginsFolders as $pluginFolder) {
            $db->transStart();

            $indexId = new IndexModel()
                ->insert(new Index([
                    'repository_url'    => $repositoryUrl,
                    'repository_folder' => $pluginFolder,
                ]));

            service('queue')
                ->push('crawls', 'crawl-plugin', [
                    'index_id' => $indexId,
                ],);

            $db->transComplete();
        }
    }
}
