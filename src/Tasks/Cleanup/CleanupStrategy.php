<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/6/19
 * Time: 3:55 PM
 */

namespace Kibb\Backup\Tasks\Cleanup;


use Illuminate\Config\Repository;
use Kibb\Backup\BackupDestination\BackupCollection;

abstract class CleanupStrategy
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    abstract public function deleteOldBackups(BackupCollection $backup);
}