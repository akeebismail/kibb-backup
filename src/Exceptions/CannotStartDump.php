<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:19 PM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class CannotStartDump extends Exception
{

    /**
     * @param $name
     * @return CannotStartDump
     */
    public static function emptyParameter($name)
    {
        return new static("Parameter `{$name}` cannot be empty.");
    }
}