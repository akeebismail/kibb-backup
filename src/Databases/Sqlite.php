<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/16/18
 * Time: 12:04 AM
 */

namespace Kibb\Backup\Databases;


use Kibb\Backup\Databases\DbDumper\DbDumper;
use Symfony\Component\Process\Process;

class Sqlite extends DbDumper
{

    public function dumpToFile(string $dumpFile)
    {
        $command = $this->getDumpCommand($dumpFile);

        $process = new Process($command);

        if (! is_null($this->timeout)){
            $process->setTimeout($this->timeout);
        }

        $process->run();
        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    public function getDumpCommand(string $dumpFile): string
    {
        $command = sprintf(
            "echo 'BEGIN IMMEDIATE;\n.dump' | '%ssqlite3' --bail '%s'",
            $this->dumpBinaryPath,
            $this->dbName
        );

        return $this->echoToFile($command,$dumpFile);
    }
}