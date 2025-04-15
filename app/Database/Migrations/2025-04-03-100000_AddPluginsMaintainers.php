<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPluginsMaintainers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'plugin_key' => [
                'type'       => 'VARCHAR',
                'constraint' => '257',
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'added_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey(['plugin_key', 'user_id'], 'pk_plugins_maintainers');
        $this->forge->addForeignKey('plugin_key', 'plugins', 'key', '', 'CASCADE', 'fk_plugins_maintainers_plugin_key');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE', 'fk_plugins_maintainers_user_id');

        $this->forge->createTable('plugins_maintainers');
    }

    public function down(): void
    {
        $this->forge->dropTable('plugins_maintainers');
    }
}
