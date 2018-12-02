<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 7:44 AM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class InvalidBackupJob extends Exception
{
    public static function noDestinationSpecified(): self
    {
        return new static('A backup job cannot run without a destination to backup to!');
    }

    public static function destinationDoesNotExist(string $diskname): self
    {
        return new static("There is no backup destination with a disk named `{$diskname}`");
    }

    public static function noFilesToBeBackedUp():self
    {
        return new static('There are no files to be backed up.');
    }
}