<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDownloads extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'version_id' => [
                'type'     => 'INT',
                'unsigned' => true,
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

        $this->forge->addPrimaryKey('id', 'pk_downloads');
        $this->forge->addForeignKey('version_id', 'versions', 'id', '', 'CASCADE', 'fk_downloads_version_id');
        $this->forge->addUniqueKey(['version_id', 'date'], 'uk_version_id_date');
        $this->forge->createTable('downloads');
    }

    public function down(): void
    {
        $this->forge->dropTable('downloads');
    }
}
