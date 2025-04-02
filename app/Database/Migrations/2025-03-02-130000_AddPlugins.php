<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\App;

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
                'official',
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

        /** @var App $appConfig */
        $appConfig = config('App');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '257',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
                'null'       => true,
            ],
            'text_searchable' => [
                'type' => 'TSVECTOR GENERATED ALWAYS AS (to_tsvector(\'simple\', regexp_replace(key, \'[/\-]\', \' \', \'g\') || coalesce(description, \'\'))) STORED',
            ],
            'icon_svg' => [
                'type'       => 'VARCHAR',
                'constraint' => $appConfig->maxIconSize,
                'null'       => true,
            ],
            'repository_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
            ],
            'manifest_root' => [
                'type'       => 'VARCHAR',
                'constraint' => '128',
            ],
            'homepage_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '512',
                'null'       => true,
            ],
            'categories' => [
                'type' => 'plugin_category ARRAY',
            ],
            'authors' => [
                'type' => 'JSONB',
            ],
            'downloads_total' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'is_updating' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'owner_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addPrimaryKey('id', 'pk_plugins');
        $this->forge->addUniqueKey('key', 'uk_plugins_key');

        $this->forge->addUniqueKey(['repository_url', 'manifest_root'], 'uk_plugins_repository_url_manifest_root');

        $this->forge->addForeignKey('owner_id', 'users', 'id', '', 'CASCADE', 'fk_plugins_owner_id');

        $this->forge->createTable('plugins');

        $this->db->query('CREATE INDEX idx_textsearch ON plugins USING GIN(text_searchable);');
        $this->db->query('ALTER TABLE plugins
                            ADD CONSTRAINT fk_plugins_repository_url_manifest_root
                                FOREIGN KEY (repository_url, manifest_root) REFERENCES index(repository_url, manifest_root)
                                ON UPDATE CASCADE
                                ON DELETE CASCADE;');
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
