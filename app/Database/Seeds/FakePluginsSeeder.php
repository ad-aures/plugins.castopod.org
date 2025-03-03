<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Entities\Plugin;
use App\Models\PluginModel;
use CodeIgniter\Database\Seeder;

class FakePluginsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $licenses = [
            'AGPL-3.0-only',
            'AGPL-3.0-or-later',
            'Apache-2.0',
            'BSL-1.0',
            'Custom',
            'GPL-3.0-only',
            'GPL-3.0-or-later',
            'LGPL-3.0-only',
            'LGPL-3.0-or-later',
            'MIT',
            'MPL-2.0',
            'Unlicense',
            'UNLICENSED',
        ];
        $categories = [
            'accessibility',
            'analytics',
            'monetization',
            'podcasting2',
            'privacy',
            'productivity',
            'seo',
        ];
        $hooks = ['rssBeforeChannel', 'rssAfterChannel', 'rssBeforeItem', 'rssAfterItem', 'siteHead'];

        $pluginModel = new PluginModel();
        for ($i = 0; $i < 4; $i++) {
            $pluginModel->insert(new Plugin([
                'name'               => $faker->slug(random_int(1, 2)) . '/' . $faker->slug(random_int(1, 2)),
                'description'        => $faker->paragraph(),
                'repository_url'     => $faker->url(),
                'repository_folder'  => '',
                'readme_markdown'    => $faker->text(),
                'homepage'           => $faker->url(),
                'license'            => $licenses[array_rand($licenses)],
                'license_markdown'   => $faker->text(),
                'categories'         => $this->getRandomValuesFrom($categories),
                'minCastopodVersion' => '2.0.0',
                'hooks'              => $this->getRandomValuesFrom($hooks),
            ]));
        }
    }

    /**
     * @param list<string> $data
     * @return list<string>
     */
    private function getRandomValuesFrom(array $data): array
    {
        $randomKeys = array_rand($data, random_int(1, 3));

        if (! is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }

        $randomValues = [];
        foreach ($randomKeys as $key) {
            $randomValues[] = $data[$key];
        }

        return $randomValues;
    }
}
