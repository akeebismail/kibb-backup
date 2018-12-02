<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 7:49 AM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class InvalidCommand extends Exception
{

    public static function create(string $reason): self
    {
        return new static($reason);
    }

}