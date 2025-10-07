<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ResetApp extends BaseCommand
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
    protected $name = 'app:reset';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Resets Castopod Plugin Repository to its initial state.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'app:reset';

    public function run(array $params): int
    {
        // Are you sure prompt
        $confirmation = CLI::prompt(
            '⚠️  ' .
            CLI::color(' DANGER ZONE ', 'white', 'red') .
            ' ⚠️' . PHP_EOL .
            CLI::color(
                'This command will reset all the Castopod Plugin Repository to its initial state: all data in the database will be wiped, and media folders will be permanently deleted.',
                'light_yellow',
            ) .
            PHP_EOL . PHP_EOL .
            'Write down "' . CLI::color('Castopod Plugin Repository', 'light_blue') . '" if you wish to continue',
            null,
            'required',
        );

        if ($confirmation !== 'Castopod Plugin Repository') {
            CLI::error('Aborting reset.');
            return EXIT_ERROR;
        }

        CLI::newLine(2);

        // delete static files
        CLI::print('Deleting avatars…', 'white');
        if (! delete_files(media_path('avatars'), true, true)) {
            CLI::error('Could not delete avatars.');

            return EXIT_ERROR;
        }
        CLI::print(' DONE!', 'light_green');

        CLI::newLine();

        CLI::print('Deleting plugin archives…', 'white');
        if (! delete_files(media_path('plugins'), true, true)) {
            CLI::error('Could not delete plugin archives.');

            return EXIT_ERROR;
        }
        CLI::print(' DONE!', 'light_green');

        CLI::newLine();

        CLI::print('Refreshing database…', 'white');

        // refresh database
        command('migrate:refresh --all');

        return 0;
    }
}
