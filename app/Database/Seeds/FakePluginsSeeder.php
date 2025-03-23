<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Entities\Enums\License;
use App\Entities\Person;
use App\Entities\Plugin;
use App\Entities\Version;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class FakePluginsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $pluginModel = new PluginModel();
        $versionModel = new VersionModel();
        for ($i = 0; $i < 10; $i++) {
            $pluginId = $pluginModel->insert(new Plugin([
                'vendor'         => $faker->slug(random_int(1, 2)),
                'name'           => $faker->slug(random_int(1, 2)),
                'description'    => $faker->paragraph(),
                'icon_svg'       => '',
                'repository_url' => $faker->url(),
                'manifest_root'  => '',
                'homepage_url'   => $faker->url(),
                'categories'     => $this->getRandomValuesFromEnum('Category'),
                'authors'        => [
                    new Person([
                        'name'  => $faker->name(),
                        'email' => $faker->email(),
                        'url'   => $faker->url(),
                    ]),
                    new Person([
                        'name'  => $faker->name(),
                        'email' => $faker->email(),
                        'url'   => $faker->url(),
                    ]),
                ],
            ]));

            foreach (['dev-main', '1.0.0', '1.0.1', '1.1.0', '1.2.0'] as $key => $version) {
                $versionModel->insert(new Version([
                    'plugin_id'            => $pluginId,
                    'tag'                  => $version,
                    'commit_hash'          => hash('sha1', $faker->text()),
                    'readme_markdown'      => $faker->text(),
                    'license'              => License::cases()[array_rand(License::cases())]->value,
                    'min_castopod_version' => '2.0.0',
                    'hooks'                => $this->getRandomValuesFromEnum('Hook'),
                    'size'                 => random_int(300000, 500000),
                    'file_count'           => random_int(3, 6),
                    'published_at'         => $version === 'dev-main' ? Time::now() : Time::now()->subYears(
                        1,
                    )->addMonths($key)
                        ->addDays(random_int(0, 15)),
                ]));
            }
        }
    }

    /**
     * @return list<string>
     */
    private function getRandomValuesFromEnum(string $enumName): array
    {
        $enum = sprintf('\App\Entities\Enums\%s', $enumName);

        $randomKeys = array_rand($enum::cases(), random_int(1, 3));

        if (! is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }

        $randomValues = [];
        foreach ($randomKeys as $key) {
            $randomValues[] = $enum::cases()[$key]->value;
        }

        return $randomValues;
    }
}
