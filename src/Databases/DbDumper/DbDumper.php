<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:07 PM
 */

namespace Kibb\Backup\Databases\DbDumper;

use Kibb\Backup\Compressors\Compressor;
use Kibb\Backup\Compressors\GzipCompressor;
use Kibb\Backup\Exceptions\CannotSetParameter;
use Kibb\Backup\Exceptions\DumpFailed;
use Symfony\Component\Process\Process;
abstract class DbDumper
{
    /** @var string */
    protected $dbName;

    /** @var string */
    protected $userName;

    /** @var string */
    protected $password;

    /** @var string  */
    protected $host = 'localhost';

    /** @var int  */
    protected $port = 3306;

    /** @var string  */
    protected $socket = '';

    /** @var int  */
    protected $timeout = 0;

    /** @var string  */
    protected $dumpBinaryPath = '';

    /**
     * @var array
     */
    protected $includeTables = [];

    /** @var array  */
    protected $excludeTables = [];

    /** @var array  */
    protected $extraOptions = [];

    /** @var object */
    protected $compressor = null;

    public static function create()
    {
        return new static();
    }
    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * @param string $dbName
     * @return $this
     */
    public function setDbName($dbName): self
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return DbDumper
     */
    public function setHost(string $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return DbDumper
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getSocket(): string
    {
        return $this->socket;
    }

    /**
     * @param string $socket
     * @return DbDumper
     */
    public function setSocket(string $socket)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return DbDumper
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getDumpBinaryPath(): string
    {
        return $this->dumpBinaryPath;
    }

    /**
     * @param string $dumpBinaryPath
     * @return DbDumper
     */
    public function setDumpBinaryPath(string $dumpBinaryPath)
    {
        if ($dumpBinaryPath !== '' && substr($dumpBinaryPath, -1) !== '/'){
            $dumpBinaryPath.='/';
        }
        $this->dumpBinaryPath = $dumpBinaryPath;

        return $this;
    }

    public function enableCompression()
    {
        $this->compressor = new GzipCompressor();
        return $this;
    }

    public function getCompressorExtension(): string
    {
        return $this->compressor->useExtension();
    }

    public function useCompressor(Compressor $compressor)
    {
        $this->compressor = $compressor;

        return $this;
    }

    /**
     * @param $includeTables
     * @return DbDumper
     * @throws CannotSetParameter
     */
    public function includeTables($includeTables)
    {
        if (!empty($this->excludeTables)){
            throw CannotSetParameter::conflictingParameters('includeTables','excludeTables');
        }
        if (! is_array($includeTables)){
            $includeTables = explode(', ',$includeTables);
        }

        $this->includeTables = $includeTables;
        return $this;
    }

    /**
     * @param $excludeTables
     * @return $this
     * @throws CannotSetParameter
     */
    public function excludeTables($excludeTables)
    {
        if (!empty($this->includeTables)){
            throw CannotSetParameter::conflictingParameters('excludeTables','includeTables');
        }

        if (! is_array($excludeTables)){
            $excludeTables = explode(', ', $excludeTables);
        }
        $this->extraOptions = $excludeTables;
        return $this;
    }

    public function addExtraOption(string  $extraOption)
    {
        if (!empty($extraOption)){
            $this->extraOptions[] = $extraOption;
        }
        return $this;
    }

    abstract public function dumpToFile(string $dumpFile);

    protected function checkIfDumpWasSuccessFul(Process $process, string  $outputFile)
    {
        if (!$process->isSuccessful()){
            throw DumpFailed::processDidNotEndSuccefully($process);
        }

        if (!file_exists($outputFile)){
            throw DumpFailed::dumpFileWasNotCreated();
        }

        if (filesize($outputFile) === 0){
            throw DumpFailed::dumpfileWasEmpty();
        }
    }

    protected function echoToFile(string $command, string $dumpFile): string
    {
        $compressor = $this->compressor
            ? ' | '.$this->compressor->userCommand()
            : '';

        $dumpFile = '"'.addcslashes($dumpFile,'\\"').'"';

        return $command.$compressor.' > '.$dumpFile;
    }
}