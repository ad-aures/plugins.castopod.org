<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDownloads extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'plugin_key' => [
                'type'       => 'VARCHAR',
                'constraint' => '257',
            ],
            'version_tag' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'count' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
        ]);

        $this->forge->addPrimaryKey(['plugin_key', 'version_tag', 'date'], 'pk_downloads');
        $this->forge->createTable('downloads');

        $this->db->query('ALTER TABLE downloads
                            ADD CONSTRAINT fk_downloads_plugin_key_version_tag
                                FOREIGN KEY (plugin_key, version_tag) REFERENCES versions(plugin_key, tag);');
    }

    public function down(): void
    {
        $this->forge->dropTable('downloads');
    }
}
