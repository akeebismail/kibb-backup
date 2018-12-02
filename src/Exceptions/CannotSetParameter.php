<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:16 PM
 */

namespace Kibb\Backup\Exceptions;

use Exception;
class CannotSetParameter extends Exception
{
    /**
     * @param $name
     * @param $conflictName
     * @return CannotSetParameter
     */
    public static function conflictingParameters($name, $conflictName)
    {
        return new static("Cannot set `{$name}` because is conflicting with parameter `{$conflictName}");
    }

}