<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 8:22 AM
 */

namespace Kibb\Backup\Commands;


use Kibb\Backup\Exceptions\InvalidCommand;

class BackupCommand extends BaseCommand
{
    /** @var string  */
    protected $signature = 'backup:run {--filename=} {--only-db} {--db-name=*} {--only-files} {--only-to-disk=} {--disable-notifications}';

    /** @var string  */
    protected $description = 'Run the backup';

    public function handle()
    {
        consoleOutput()->comment('Starting backup...');
        $disableNotificatios = $this->option('disable-notifications');

        try{
            $this->guardAgainstInvalidOptions();
        }catch (Exeption $exception){
            consoleOutput()->error("Backup failed because: {$exception->getMessage()}");

            if (! $disableNotificatios){
                event(new BackupHasFailed($exception));
            }

            return 1;
        }
    }

    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw InvalidCommand::create('Cannot use `only-db` and `only-files` together');
        }
    }
}