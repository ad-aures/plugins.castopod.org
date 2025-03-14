<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndex extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'repository_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
            ],
            'repository_folder' => [
                'type'       => 'VARCHAR',
                'constraint' => '256',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id', 'pk_index');
        $this->forge->addUniqueKey(['repository_url', 'repository_folder'], 'uk_repository');

        $this->forge->createTable('index');
    }

    public function down(): void
    {
        $this->forge->dropTable('index');
    }
}
