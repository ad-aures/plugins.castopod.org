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
            'plugin_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 257,
            ],
            'tag' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
            ],
            'commit_hash' => [
                'type'       => 'CHAR',
                'constraint' => '40',
            ],
            'readme_markdown' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'license' => [
                'type'    => 'plugin_license',
                'default' => 'UNLICENSED',
            ],
            'min_castopod_version' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
            ],
            'hooks' => [
                'type' => 'plugin_hook ARRAY',
            ],
            'size' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'file_count' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'downloads_total' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'published_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id', 'pk_versions');
        $this->forge->addUniqueKey(['plugin_key', 'tag'], 'uk_versions_plugin_key_tag');
        $this->forge->addForeignKey('plugin_key', 'plugins', 'key', '', 'CASCADE', 'fk_versions_plugin_key');
        $this->forge->createTable('versions');
    }

    public function down(): void
    {
        $this->forge->dropTable('versions');
    }
}
