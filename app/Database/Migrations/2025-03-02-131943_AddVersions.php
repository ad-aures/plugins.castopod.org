<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVersions extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'plugin_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'tag' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
            ],
            'commit_hash' => [
                'type'       => 'CHAR',
                'constraint' => '40',
            ],
            'published_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true, false, 'pk_versions');
        $this->forge->addForeignKey('plugin_id', 'plugins', 'id', '', 'CASCADE', 'fk_versions_plugin_id');
        $this->forge->addUniqueKey(['plugin_id', 'tag'], 'uk_plugin_id_tag');
        $this->forge->createTable('versions');
    }

    public function down(): void
    {
        $this->forge->dropTable('versions');
    }
}
