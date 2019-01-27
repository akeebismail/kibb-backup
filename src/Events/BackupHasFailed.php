<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/27/19
 * Time: 10:25 PM
 */

namespace Kibb\Backup\Events;


use Kibb\Backup\BackupDestination\BackupDestination;

class BackupHasFailed
{
    /** @var \Exception */
    public $exception;

    /** @var BackupDestination|null */
    public $backupDestination;

    public function __construct(Exception $exception, BackupDestination $backupDestination)
    {
        $this->exception = $exception;
        $this->backupDestination = $backupDestination;
    }
}