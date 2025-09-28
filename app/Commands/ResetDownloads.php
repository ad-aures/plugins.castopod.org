<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ResetDownloads extends BaseCommand
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
    protected $name = 'downloads:reset';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Resets all total downloads to 0.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'downloads:reset';

    /**
     * Resets all total downloads to 0.
     */
    public function run(array $params): int
    {
        $db = db_connect();

        $db->transBegin();

        if (! $db->table('versions')->set('downloads_total', 0)->update()) {
            CLI::error(
                sprintf(
                    'Error when resetting total downloads: %s - %s ',
                    (string) $db->error()['code'],
                    (string) $db->error()['message'],
                ),
            );

            return 1;
        }

        setting('Downloads.lastComputeDate', null);

        $db->transComplete();

        CLI::write('All total downloads have been successfully reset!', 'light_green');

        return 0;
    }
}
