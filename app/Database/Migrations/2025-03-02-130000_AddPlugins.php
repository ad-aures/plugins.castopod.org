<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlugins extends Migration
{
    public function up(): void
    {
        $licenseEnumQuery = <<<SQL
            CREATE TYPE plugin_license AS ENUM (
                'AGPL-3.0-only',
                'AGPL-3.0-or-later',
                'Apache-2.0',
                'BSL-1.0',
                'Custom',
                'GPL-3.0-only',
                'GPL-3.0-or-later',
                'LGPL-3.0-only',
                'LGPL-3.0-or-later',
                'MIT',
                'MPL-2.0',
                'Unlicense',
                'UNLICENSED'
            );
        SQL;
        $this->db->query($licenseEnumQuery);

        $categoryEnumQuery = <<<SQL
            CREATE TYPE plugin_category AS ENUM (
                'accessibility',
                'analytics',
                'monetization',
                'podcasting2',
                'privacy',
                'productivity',
                'seo'
            );
        SQL;
        $this->db->query($categoryEnumQuery);

        $hookEnumQuery = <<<SQL
            CREATE TYPE plugin_hook AS ENUM (
                'rssBeforeChannel',
                'rssAfterChannel',
                'rssBeforeItem',
                'rssAfterItem',
                'siteHead'
            );
        SQL;
        $this->db->query($hookEnumQuery);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendor' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
            ],
            'text_searchable' => [
                'type' => 'TSVECTOR GENERATED ALWAYS AS (to_tsvector(\'simple\', vendor || \' \' || name || \' \' || coalesce(description, \'\'))) STORED',
            ],
            'icon_svg' => [
                'type' => 'TEXT',
            ],
            'repository_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
            ],
            'repository_folder' => [
                'type'       => 'VARCHAR',
                'constraint' => '256',
            ],
            'homepage' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
            ],
            'categories' => [
                'type' => 'plugin_category ARRAY',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id', 'pk_plugins');
        $this->forge->addUniqueKey(['vendor', 'name'], 'uk_name');
        $this->forge->createTable('plugins');

        $this->db->query('CREATE INDEX idx_textsearch ON plugins USING GIN(text_searchable);');
    }

    public function down(): void
    {
        $this->forge->dropTable('plugins');

        $this->db->query(<<<SQL
            DROP TYPE plugin_hook;
            DROP TYPE plugin_category;
            DROP TYPE plugin_license;
        SQL);
    }
}
