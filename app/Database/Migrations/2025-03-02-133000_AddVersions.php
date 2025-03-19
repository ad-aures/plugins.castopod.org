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
            'commit' => [
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
            'installs_total' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'published_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id', 'pk_versions');
        $this->forge->addForeignKey('plugin_id', 'plugins', 'id', '', 'CASCADE', 'fk_versions_plugin_id');
        $this->forge->addUniqueKey(['plugin_id', 'tag'], 'uk_plugin_id_tag');
        $this->forge->createTable('versions');
    }

    public function down(): void
    {
        $this->forge->dropTable('versions');
    }
}
