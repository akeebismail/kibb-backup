<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/16/18
 * Time: 9:33 AM
 */

namespace Kibb\Backup\Tasks\Backup;


use Illuminate\Support\Collection;
use Kibb\Backup\BackupDestination\BackupDestinationFactory;

class BackupJobFactory
{

    public static function createFromArray(array $config): BackupJob
    {
        return (new BackupJob())
            ->setFileSelection(static::createFileSelection($config['backup']['source']['files']))
            ->setDbDumpers(static::createDbDumpers($config['backup']['source']['databases']))
            ->setBackupDestinations(BackupDestinationFactory::createFromArray($config['backup']));
    }

    protected static function createFileSelection(array $sourceFiles): FileSelection
    {
        return FileSelection::create($sourceFiles['include'])
            ->excludeFilesFrom($sourceFiles['exclude'])
            ->shouldFollowLinks(isset($sourceFiles['followLinks']) && $sourceFiles['followLinks']);
    }

    protected static function createDbDumpers(array $dbConnectionNames): Collection
    {
        return collect($dbConnectionNames)->
            mapWithKeys(function (string $dbConnectionName){
                return [$dbConnectionName => DbDumperFactory::createFromConnection($dbConnectionName)];
        });
    }
}