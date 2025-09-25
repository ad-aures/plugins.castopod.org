<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Entities\Index;
use App\Models\IndexModel;
use CodeIgniter\Database\Seeder;

class CrawlIndexSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Index[] */
        $index = new IndexModel()
            ->findAll();

        foreach ($index as $pluginIndex) {
            service('queue')->push('crawls', 'plugin-crawl', [
                'index_id' => $pluginIndex->id,
            ]);
        }
    }
}
