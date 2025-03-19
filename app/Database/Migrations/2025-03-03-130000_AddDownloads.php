<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstalls extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
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

        $this->forge->addPrimaryKey(['version_id', 'date'], 'pk_installs');
        $this->forge->addForeignKey('version_id', 'versions', 'id', '', 'CASCADE', 'fk_installs_version_id');
        $this->forge->createTable('installs');
    }

    public function down(): void
    {
        $this->forge->dropTable('installs');
    }
}
