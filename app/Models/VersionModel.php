<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Version;
use App\Exceptions\PluginNotFoundException;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\RawSql;

class VersionModel extends BaseModel
{
    protected $table = 'versions';

    protected $returnType = Version::class;

    protected $allowedFields = [
        'plugin_key',
        'tag',
        'commit_hash',
        'readme_markdown',
        'license',
        'min_castopod_version',
        'hooks',
        'size',
        'file_count',
        'downloads_total',
        'published_at',
    ];

    protected array $casts = [
        'license' => 'enum[License]',
        'hooks'   => 'enum-array[Hook]',
    ];

    /**
     * @return Version[]
     */
    public function getAllPluginVersions(string $pluginKey): array
    {
        $cacheName = sprintf('plugin#%s-versions', str_replace('/', '_', $pluginKey));

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'plugin_key' => $pluginKey,
            ])->orderBy('published_at', 'DESC')
                ->findAll();

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Version[] $found */
        return $found;
    }

    public function getLatestPluginVersion(string $pluginKey): Version
    {
        $cacheName = sprintf('plugin#%s-latest_version', str_replace('/', '_', $pluginKey));

        if (! ($found = cache($cacheName))) {
            // SELECT *
            // FROM versions v
            // WHERE plugin_id = $pluginId
            // AND published_at = (
            //     SELECT COALESCE(
            //         (SELECT MAX(published_at) FROM versions WHERE plugin_id = $pluginId AND tag NOT LIKE 'dev-%'),
            //         (SELECT MAX(published_at) FROM versions WHERE plugin_id = $pluginId)
            //     )
            // )
            $found = $this->where([
                'plugin_key' => $pluginKey,
            ])->where('published_at', static function (BaseBuilder $builder) use ($pluginKey) {
                $db = db_connect();
                $subquery = $db->table('versions')
                    ->selectMax('published_at')
                    ->where([
                        'plugin_key'   => $pluginKey,
                        'tag NOT LIKE' => 'dev-%',
                    ]);
                $subquery2 = $db->table('versions')
                    ->selectMax('published_at')
                    ->where('plugin_key', $pluginKey);
                $rawQuery = sprintf(
                    'COALESCE((%s), (%s))',
                    $subquery->getCompiledSelect(),
                    $subquery2->getCompiledSelect(),
                );
                $builder->select(new RawSql($rawQuery));
            })->first();

            if (! $found instanceof Version) {
                throw PluginNotFoundException::forVersionNotFound($pluginKey, 'latest');
            }

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Version $found */
        return $found;
    }

    public function getPluginVersion(string $pluginKey, string $tag): Version
    {
        $cacheName = sprintf('plugin#%s-version#%s', str_replace('/', '_', $pluginKey), $tag);

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'plugin_key' => $pluginKey,
                'tag'        => $tag,
            ])->first();

            if (! $found instanceof Version) {
                throw PluginNotFoundException::forVersionNotFound($pluginKey, $tag);
            }

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var Version $found */
        return $found;
    }
}
