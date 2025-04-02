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
            'manifest_root' => [
                'type'       => 'VARCHAR',
                'constraint' => '256',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
            ],
            'submitted_by' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id', 'pk_index');
        $this->forge->addUniqueKey(['repository_url', 'manifest_root'], 'uk_repository');

        // TODO: what to do when a user gets deleted? All of their plugins are deleted?
        $this->forge->addForeignKey('submitted_by', 'users', 'id', '', '', 'fk_index_submitted_by');

        $this->forge->createTable('index');
    }

    public function down(): void
    {
        $this->forge->dropTable('index');
    }
}
