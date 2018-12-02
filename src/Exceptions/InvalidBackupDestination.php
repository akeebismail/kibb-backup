<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 7:40 AM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class InvalidBackupDestination extends Exception
{
    public static function diskNotSet(): self
    {
        return new static ('There is no disk set for the backup destination');
    }
}