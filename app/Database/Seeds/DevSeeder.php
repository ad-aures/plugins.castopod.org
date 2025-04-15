<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('DevSuperadminSeeder');
        $this->call('DevUsersSeeder');
        $this->call('OfficialPluginsSeeder');
    }
}
