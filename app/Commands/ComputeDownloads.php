<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\RawSql;
use DateTime;

class ComputeDownloads extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'downloads:compute';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Computes all version\'s total downloads before today.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'downloads:compute';

    /**
     * Computes all download hits before current date
     */
    public function run(array $params): int
    {
        $dateSettingKey = 'Downloads.lastComputeDate';
        $currentDate = new DateTime();

        // get current date in settings, set it if not already set
        $lastDownloadsComputeDate = setting($dateSettingKey);
        if (! $lastDownloadsComputeDate instanceof DateTime) {
            $lastDownloadsComputeDate = $currentDate;
        } elseif ($lastDownloadsComputeDate->format('Y-m-d') === $currentDate->format('Y-m-d')) {
            CLI::write('Nothing to do…', 'black');
            CLI::write(
                sprintf('Downloads have already been computed today at %s', $lastDownloadsComputeDate->format('H:i:s')),
                'light_yellow',
            );

            return 0;
        }

        /**
         * UPDATE versions
         * SET downloads_total = versions.downloads_total + subquery.downloads_total
         * FROM (
         *     SELECT plugin_key, version_tag, SUM(count) as downloads_total
         *     FROM downloads
         *     WHERE date < CURRENT
         *     GROUP BY plugin_key, version_tag
         * ) AS subquery
         * WHERE versions.plugin_key = subquery.plugin_key
         *   AND tag = subquery.version_tag;
         */

        $condition = 'date < \'' . $currentDate->format('Y-m-d') . '\'';

        if ($lastDownloadsComputeDate !== $currentDate) {
            $condition .= ' AND date >= \'' . $lastDownloadsComputeDate->format('Y-m-d') . '\'';
        }

        $rawQuery = new RawSql(<<<SQL
            WITH subquery AS (
              SELECT plugin_key, version_tag, SUM(count) as downloads_total
              FROM downloads
              WHERE {$condition}
              GROUP BY plugin_key, version_tag
            )
            UPDATE versions
            SET downloads_total = versions.downloads_total + subquery.downloads_total
            FROM subquery
            WHERE versions.plugin_key = subquery.plugin_key AND tag = subquery.version_tag;
        SQL);

        $db = db_connect();
        $db->transBegin();

        if (! $db->query((string) $rawQuery)) {
            CLI::error(
                sprintf(
                    'Error when computing total downloads: %s - %s ',
                    (string) $db->error()['code'],
                    (string) $db->error()['message'],
                ),
            );

            return 1;
        }

        // set last downloads compute date
        setting($dateSettingKey, $lastDownloadsComputeDate);

        $db->transComplete();

        CLI::write('Successfully computed total downloads!', 'light_green');

        return 0;
    }
}
