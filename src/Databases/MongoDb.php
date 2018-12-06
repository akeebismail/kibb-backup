<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:05 PM
 */

namespace Kibb\Backup\Databases;


use Kibb\Backup\Databases\DbDumper\DbDumper;
use Kibb\Backup\Exceptions\CannotStartDump;
use Symfony\Component\Process\Process;

class MongoDb extends DbDumper
{

    /** @var null/string */
    protected $port = 27017;

    /** @var null/string */
    protected $collection = null;

    /** @var null|string */
    protected $authenticationDatabase = null;

    public function dumpToFile(string $dumpFile)
    {
        $this->guardAgainstIncompleteCredentials();
        $command = $this->getDumpCommand($dumpFile);

        $process = new Process($command);
        if (!is_null($this->timeout)){
            $process->setTimeout($this->timeout);
        }

        $process->run();
        $this->checkIfDumpWasSuccessFul($process, $dumpFile);
    }

    /**
     * @throws CannotStartDump
     */
    protected function guardAgainstIncompleteCredentials()
    {
        foreach (['dbName','host'] as $item){
            if (strlen($this->$item) == 0){
                throw CannotStartDump::emptyParameter($item);
            }
        }
    }

    /**
     * @param string $collection
     * @return $this
     */
    public function setCollection(string $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @param string $authenticationDatabase
     * @return $this
     */
    public function setAuthenticationDatabase(string $authenticationDatabase)
    {
        $this->authenticationDatabase = $authenticationDatabase;
        return $this;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getDumpCommand(string $filename) : string
    {
        $command = [
          "'{$this->dumpBinaryPath}mongodump'",
          "--db {$this->dbName}",
          "--archive"
        ];
        if ($this->userName){
            $command[] = "--username '$this->userName}'";
        }
        if ($this->password){
            $command[] = "--password '{$this->password}'";
        }
        if (isset($this->host)){
            $command[] = "--host {$this->host}";
        }
        if (isset($this->port)){
            $command[] = "--port {$this->port}";
        }
        if (isset($this->collection)){
            $command[] = "--collection {$this->collection}";
        }

        if ($this->authenticationDatabase){
            $command[] = "--authenticationDatabase {$this->authenticationDatabase}";
        }

        return $this->echoToFile(implode(' ',$command), $filename);
    }


}