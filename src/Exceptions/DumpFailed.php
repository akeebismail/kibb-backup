<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:09 PM
 */

namespace Kibb\Backup\Exceptions;
use Exception;
use Symfony\Component\Process\Process;
class DumpFailed extends Exception
{

    /**
     * @param Process $process
     *
     * @return DumpFailed
     */

    public static function processDidNotEndSuccefully(Process $process)
    {

        return new static("The dump process failed with exitcode {$process->getExitCode()} :
        {$process->getExitCodeText()} : {$process->getErrorOutput()}");
    }

    /**
     * @return DumpFailed
     */
    public static function dumpFileWasNotCreated()
    {
        return new static('The dumpfile could not be created');
    }

    /**
     * @return DumpFailed
     */
    public static function dumpfileWasEmpty()
    {
        return new static('The created dumpfile is empty');
    }
}