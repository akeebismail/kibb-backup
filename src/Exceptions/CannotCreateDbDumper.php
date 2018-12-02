<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 7:42 AM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class CannotCreateDbDumper extends Exception
{

    public static function unsupportedDriver(string $driver): self
    {
        return new static("Cannot create a dumper for db driver `{$driver}`");
    }

}