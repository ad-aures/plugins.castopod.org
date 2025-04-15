<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\PluginMaintainerModel;
use App\Models\PluginModel;
use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class DevMaintainersSeeder extends Seeder
{
    public function run(): void
    {
        $plugins = new PluginModel()
            ->findAll();

        $pluginMaintainerModel = new PluginMaintainerModel();
        foreach ($plugins as $plugin) {
            $pluginMaintainerModel->builder()
                ->ignore(true)
                ->insert([
                    'plugin_key' => $plugin->key,
                    'user_id'    => random_int(2, 3),
                    'created_at' => Time::now(),
                ]);
        }
    }
}
