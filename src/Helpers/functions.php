<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 12:07 PM
 */

use Kibb\Backup\Helpers\ConsoleOutPut;

function consoleOutput(): ConsoleOutPut
{
    return app(ConsoleOutPut::class);
}