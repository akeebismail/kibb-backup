<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/27/19
 * Time: 10:30 PM
 */

namespace Kibb\Backup\Events;


use Kibb\Backup\BackupDestination\BackupDestination;

class BackupWasSuccessful
{
    /** @var \Kibb\Backup\BackupDestination\BackupDestination */
    public $backupDestination;

    public function __construct(BackupDestination $backupDestination)
    {
        $this->backupDestination = $backupDestination;
    }
}