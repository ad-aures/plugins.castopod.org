<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarPathForUsers extends Migration
{
    public function up(): void
    {
        $fields = [
            'avatar_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down(): void
    {
        $fields = ['avatar_path'];

        $this->forge->dropColumn('users', $fields);
    }
}
