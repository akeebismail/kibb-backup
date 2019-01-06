<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/6/19
 * Time: 3:55 PM
 */

namespace Kibb\Backup\Tasks\Cleanup;


use Illuminate\Support\Collection;
use Kibb\Backup\BackupDestination\BackupDestination;
use Kibb\Backup\Helpers\Format;

class CleanupJob
{
    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;
    /** @var \Kibb\Backup\Tasks\Cleanup\CleanupStrategy */
    protected $strategy;

    /** @var bool */
    protected $sendNotifications = true;

    public function __construct(Collection $backupDestinations, CleanupStrategy $strategy, bool $disableNotifications = false)
    {
        $this->backupDestinations = $backupDestinations;
        $this->strategy = $strategy;
        $this->sendNotifications = ! $disableNotifications;
    }

    public function run()
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination){
           try{
               if (! $backupDestination->isReachable()){
                   throw new \Exception("Could not connect to disk {$backupDestination->diskName()} because: {$backupDestination->connectionError()}");
               }
               consoleOutput()->info("Cleaning backups of {$backupDestination->backupName()} on disk {$backupDestination->diskName()}...");
               $this->strategy->deleteOldBackups($backupDestination->backups());
               $this->sendNotifications();
               $usedStorage = Format::humanReadableSize($backupDestination->fresh()->usedStorage());
               consoleOutput()->info("Used storage after cleanup: {$usedStorage}.");
           } catch(\Exception $exception){
               consoleOutput()->error("Cleanup failed because: {$exception->getMessage()}.");
               $this->sendNotification();
               throw $exception;
           }
        });
    }

    protected function sendNotification($notificaion)
    {
        if ($this->sendNotifications){
            event($notificaion);
        }
    }

}