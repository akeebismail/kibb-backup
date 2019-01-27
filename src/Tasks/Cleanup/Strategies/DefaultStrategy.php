<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/6/19
 * Time: 3:54 PM
 */

namespace Kibb\Backup\Tasks\Cleanup\Strategies;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Kibb\Backup\BackupDestination\Backup;
use Kibb\Backup\BackupDestination\BackupCollection;
use Kibb\Backup\Tasks\Cleanup\CleanupStrategy;
use Kibb\Backup\Tasks\Cleanup\Period;

class DefaultStrategy extends CleanupStrategy
{
    /** @var \Kibb\Backup\BackupDestination\Backup */
    protected $newestBackup;

    public function deleteOldBackups(BackupCollection $backups)
    {
        //Don't ever delete the newest backup
        $this->newestBackup = $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        $backupsPerPeriod = $dateRanges->map(function (Period $period) use($backups){
            return $backups->filter(function (Backup $backup) use ($period){
                return $backup->date()->between($period->startDate(), $period->enDate());
            });
        });
        $backupsPerPeriod['daily'] = $this->groupByDateFormat($backupsPerPeriod['daily'],'Ymd');
        $backupsPerPeriod['weekly'] = $this->groupByDateFormat($backupsPerPeriod['weekly'],'YW');
        $backupsPerPeriod['monthly'] = $this->groupByDateFormat($backupsPerPeriod['monthly'],'Ym');
        $backupsPerPeriod['yearly'] = $this->groupByDateFormat($backupsPerPeriod['yearly'],'Y');

        $this->removeBackupsForAllPeriodsExceptionOne($backupsPerPeriod);
        $this->removeBackupsOlderThan($dateRanges['yearly']->endDate(), $backups);
        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups);
    }

    protected function calculateDateRanges(): Collection
    {
        $config = $this->config->get('backup.cleanup.defaultStrategy');

        $daily = new Period(
            Carbon::now()->subDays($config['keepAllBackupsForDays']),
            Carbon::now()
                ->subDays($config['keepAllBackupsForDays'])
                ->subDays($config['keepDailyBackupsForDays'])
        );

        $weekly = new Period(
            $daily->enDate(),
            $daily->enDate()
                ->subWeeks($config['keepWeeklyBackupsForWeeks'])
        );

        $monthly = new Period(
            $weekly->enDate(),
            $weekly->enDate()->subMonths($config['keepMonthlyBackupsForMonths'])
        );

        $yearly = new Period(
            $monthly->enDate(),
            $monthly->enDate()->subYears($config['keepYearlyBackupsForYears'])
        );

        return collect(compact('daily','weekly','monthly','yearly'));
    }

    protected function groupByDateFormat(Collection $backups, string $dateFormat) : Collection
    {
        return $backups->groupBy(function (Backup $backup) use ($dateFormat){
           return $backup->date()->format($dateFormat);
        });
    }

    protected function removeBackupsForAllPeriodsExceptionOne(Collection $backupsPerPeroid)
    {
        $backupsPerPeroid->each(function (Collection $groupBackupByDateProperty, string $periodName){
            $groupBackupByDateProperty->each(function (Collection $group){
                $group->shift();
                $group->each(function (Backup $backup){
                    $backup->delete();
                });
            });
        });
    }

    protected function removeBackupsOlderThan(Carbon $endDate, BackupCollection $backups)
    {
        $backups->filter(function (Backup $backup) use ($endDate){
            return $backup->exists() && $backup->date()->lt($endDate);
        })->each(function (Backup $backup){
            $backup->delete();
        });
    }

    protected function removeOldBackupsUntilUsingLessThanMaximumStorage(BackupCollection $backups)
    {
        if (! $oldest = $backups->oldest()){
            return ;
        }

        $maximumSize = $this->config->get('backup.cleanup.defaultStrategy.deleteOldestBackupsWhenUsingMoreMegabytesThan')
            * 1024 * 1024;
        if (($backups->size() + $this->newestBackup->size()) <= $maximumSize){
            return;
        }
        $oldest->delete();
        $backups = $backups->filter->exists();

        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups);
    }
}