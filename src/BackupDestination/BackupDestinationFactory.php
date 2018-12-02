<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 11/28/18
 * Time: 12:32 AM
 */

namespace Kibb\Backup\BackupDestination;


use Illuminate\Support\Collection;

class BackupDestinationFactory
{

    public static function createFromArray(array $config): Collection
    {
        return collect($config['destination']['disks'])
            ->map(function ($filesystemName) use ($config){
               return BackupDestination::create($filesystemName,$config['name']);
            });
    }
}