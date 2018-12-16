<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 12:12 PM
 */

namespace Kibb\Backup\Tasks\Backup;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Kibb\Backup\BackupDestination\BackupDestination;
use Kibb\Backup\Compressors\GzipCompressor;
use Kibb\Backup\Databases\DbDumper\DbDumper;
use Kibb\Backup\Databases\Sqlite;
use Kibb\Backup\Exceptions\InvalidBackupJob;
use Kibb\Backup\TemporaryDirectory\TemporaryDirectory;

class BackupJob
{
    /** @var \Kibb\Backup\Tasks\Backup\FileSelection */
    protected $fileSelection;

    /** @var \Illuminate\Support\Collection */
    protected $dbDumpers;

    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;

    /** @var string */
    protected $filename;

    /** @var \Kibb\Backup\TemporaryDirectory\TemporaryDirectory */
    protected $temporaryDirectory;

    /** @var bool */
    protected $sendNotifications = true;

    public function __construct()
    {
        $this->backupDestinations = new Collection();
    }

    public function dontBackupFilesystem():self
    {
        $this->fileSelection = FileSelection::create();
        return $this;
    }

    public function onlyDbName(array $allowDbNames):self
    {
        $this->dbDumpers = $this->dbDumpers->filter(
            function (DbDumper $dbDumper, string $connection) use ($allowDbNames){
                return in_array($connection, $allowDbNames);
            });
        return $this;
    }

    public function dontBackupDatabases(): self
    {
        $this->dbDumpers = new Collection();
        return $this;
    }

    public function disableNotifications(): self
    {
        $this->sendNotifications = false;
        return $this;
    }

    public function setDefaultFilename(): self
    {
        $this->filename = Carbon::now()->format('Y-m-d-H-i-s').'zip';
        return $this;
    }

    public function setFileSelection(FileSelection $fileSelection): self
    {
        $this->fileSelection = $fileSelection;
        return $this;
    }
    public function setDbDumpers(Collection $dbDumpers): self
    {
        $this->dbDumpers = $dbDumpers;
        return $this;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function onlyBackupTo(string $diskName): self
    {
        $this->backupDestinations = $this->backupDestinations->filter(
            function (BackupDestination $backupDestination) use ($diskName){
                return $backupDestination->diskName() === $diskName;
            });

        if (! count($this->backupDestinations)){
            throw InvalidBackupJob::destinationDoesNotExist($diskName);
        }

        return $this;
    }

    public function setBackupDestinations(Collection $backupDestinations): self
    {
        $this->backupDestinations = $backupDestinations;

        return $this;
    }

    public function run()
    {
        $temporaryDirectoryPath = config('backup.backup.temporary_directory')?? storage_path('app/backup-temp');
        $this->temporaryDirectory = (new TemporaryDirectory($temporaryDirectoryPath))
            ->name('temp')
            ->force()
            ->create()
            ->empty();
        try{
            if (! count($this->backupDestinations)){
                throw InvalidBackupJob::noDestinationSpecified();
            }

            $manifest = $this->createBackupManifest();
            if (! $manifest->count()){
                throw InvalidBackupJob::noFilesToBeBackedUp();
            }

            if (! $manifest->count()){
                throw InvalidBackupJob::noFilesToBeBackedUp();
            }

            $zipFile = $this->createZipContainingEveryFileInManifest($manifest);
            $this->copyToBackDestination($zipFile);
        }catch (\Exception $exception) {
            consoleOutput()->error("Backup failed because {$exception->getMessage()}." . PHP_EOL . $exception->getTraceAsString());
            $this->sendNotification('');
            throw  $exception;
        }
        $this->temporaryDirectory->delete();
    }

    protected function createBackupManifest(): Manifest
    {
        $databaseDumps = $this->dumpDatabases();
        consoleOutput()->info('Determining files to backup...');
        $manifest = Manifest::create($this->temporaryDirectory->path('manifest.txt'))
            ->addFiles($databaseDumps)
            ->addFiles($this->filesToBeBackedUp());
        $this->sendNotification('');
        return $manifest;
    }

    public function filesToBeBackedUp()
    {
        $this->fileSelection->excludeFilesFrom($this->directoriesUsedByBackupJob());

        return $this->fileSelection->selectedFiles();
    }

    protected function directoriesUsedByBackupJob(): array
    {
        return $this->backupDestinations
            ->filter(function (BackupDestination $backupDestination){
                return $backupDestination->filesystemType() === 'local';
            })
            ->map(function (BackupDestination $backupDestination){
                return $backupDestination->disk()->getDriver()->getAdapter()->applyPathPrefix('').$backupDestination->backupName();
            })
            ->each(function (string $backupDestinationDirectory){
                $this->fileSelection->excludeFilesFrom($backupDestinationDirectory);
            })
            ->push($this->temporaryDirectory->path())
            ->toArray();
    }

    protected function createZipContainingEveryFileInManifest(Manifest $manifest)
    {
        consoleOutput()->info("Zipping {$manifest->count()} files...");
        $pathToZip = $this->temporaryDirectory->path(config('backup.backup.destination.filename_prefix').$this->filename);
        $zip = Zip::createForManifest($manifest, $pathToZip);
        consoleOutput()->info("Created zip containing {$zip->count()} files. Size is {$zip->humanReadableSize()}");
        $this->sendNotification('');

        return $pathToZip;
    }
    /**
     * Dumps the databases to the given directory
     * Returns an array with paths to the dump files
     *
     * @return array
     *
     */

    protected function dumpDatabases(): array
    {
        return $this->dbDumpers->map(function (DbDumper $dbDumper){
           consoleOutput()->info("Dumping database {$dbDumper->getDbName()}...");
           $dbTyoe = mb_strtolower(basename(str_replace('\\','/',get_class($dbDumper))));
           $dName = $dbDumper instanceof Sqlite ? 'database' : $dbDumper->getDbName();
           $fileName = "{$dbTyoe}-{$dName}.sql";
           if (config('backup.backup.gzip_database_dump')){
               $dbDumper->useCompressor(new GzipCompressor());
               $fileName .= '.'.$dbDumper->getCompressorExtension();
           }

           if ($compressor = config('backup.backup.database_dump_compressor')){
               $dbDumper->useCompressor(new $compressor());
               $fileName .= '.'.$dbDumper->getCompressorExtension();
           }

           $temporaryFilePath = $this->temporaryDirectory->path('db-dumps'.DIRECTORY_SEPARATOR.$fileName);
           $dbDumper->dumpToFile($temporaryFilePath);
           return $temporaryFilePath;
        })->toArray();
    }

    protected function copyToBackDestination(string $path)
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) use ($path){
           try{
               consoleOutput()->info("Copying zip to disk named {$backupDestination->diskName()}...");
               $backupDestination->write($path);
               consoleOutput()->info("Successfully copied zip to a disk named {$backupDestination->diskName()}. ");
               $this->sendNotification('');
           } catch (\Exception $exception){
               consoleOutput()->error("Copying zip failed bacause: {$exception->getMessage()}.");
               $this->sendNotification('');
           }
        });
    }

    protected function sendNotification($notification)
    {
        if ($this->sendNotifications){
            event($notification);
        }
    }
}