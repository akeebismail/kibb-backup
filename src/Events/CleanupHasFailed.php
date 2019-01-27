<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/27/19
 * Time: 10:34 PM
 */

namespace Kibb\Backup\Events;


use Kibb\Backup\BackupDestination\BackupDestination;
use Exception;
class CleanupHasFailed
{
    /** @var \Kibb\Backup\BackupDestination\BackupDestination */
    public $backupDestination;

    /** @var \Exception */
    public $exception;

    public function __construct(Exception $exception, BackupDestination $backupDestination)
    {
        $this->exception = $exception;

        $this->backupDestination = $backupDestination;
    }
}