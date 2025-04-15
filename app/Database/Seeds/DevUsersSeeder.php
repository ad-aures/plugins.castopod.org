<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Entities\User;
use CodeIgniter\Database\Seeder;

class DevUsersSeeder extends Seeder
{
    public function run(): void
    {
        $usersData = [
            [
                'username' => 'john',
                'email'    => 'john@castopod.local',
                'password' => 'castopod',
            ],
            [
                'username' => 'jane',
                'email'    => 'jane@castopod.local',
                'password' => 'castopod',
            ],
        ];

        // Get the User Provider (UserModel by default)
        $users = auth()
            ->getProvider();

        foreach ($usersData as $userData) {
            $user = new User($userData);

            $avatarPath = save_gravatar($user);

            if (! $avatarPath) {
                continue;
            }

            $user->avatar_path = $avatarPath;

            $users->save($user);
        }
    }
}
