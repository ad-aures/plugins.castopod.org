<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Download;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\RawSql;
use CodeIgniter\I18n\Time;

class DownloadModel extends BaseModel
{
    protected $table = 'downloads';

    protected $returnType = Download::class;

    protected $allowedFields = ['plugin_key', 'version_tag', 'date', 'count'];

    /**
     * @return false|int|list<string>
     */
    public function incrementVersionDownloads(string $pluginKey, string $versionTag): false|int|array
    {
        /**
         * INSERT INTO `downloads` (`version_id`, `date`)
         * VALUES (1, '2025-03-20')
         * ON CONFLICT(`version_id`,`date`)
         * DO UPDATE SET `count` = `excluded`.`count` + 1;
         */

        /** @var BaseBuilder $builder */
        $builder = $this
            ->builder();

        return $builder
            ->onConstraint(['plugin_key', 'version_tag', 'date'])
            // @phpstan-ignore argument.type
            ->updateFields([
                'count' => new RawSql('"downloads"."count" + 1'),
            ])
            ->upsert([
                'plugin_key'  => $pluginKey,
                'version_tag' => $versionTag,
                'date'        => Time::now()->format('Y-m-d'),
            ]);
    }
}
