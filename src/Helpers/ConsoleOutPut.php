<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 8:23 AM
 */

namespace Kibb\Backup\Helpers;


class ConsoleOutPut
{
    /** @var \Illuminate\Console\OutputStyle */
    protected $output;

    /**
     * @param \Illuminate\Console\OutputStyle $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function __call(string $method, array $arguments)
    {
        $consoleOutput = app(static::class);

        if (!$consoleOutput->output){
            return ;
        }

        $consoleOutput->output->$method($arguments[0]);
    }
}