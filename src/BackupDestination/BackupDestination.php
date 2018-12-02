<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 11/28/18
 * Time: 12:32 AM
 */

namespace Kibb\Backup\BackupDestination;


use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Carbon\Carbon;

class BackupDestination
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;
    /** @var string */
    protected $diskName;

    /** @var string */
    protected $backupName;

    /** @var Exception */
    public $connectionError;

    /** @var null|\Kibb\Backup\BackupDestination\BackupCollection */
    protected $backupCollectionCache = null;

    public function __construct(Filesystem $disk = null,string $backupName,string $diskName)
    {
        $this->disk = $disk;
        $this->diskName = $diskName;
        $this->backupName = preg_replace('/[^a-zA-Z0-9.]/','-',$backupName);
    }
    public function disk(): Filesystem
    {
        return $this->disk;
    }

    public function diskName(): string
    {
        return $this->diskName;
    }

    public function filesystemType(): string
    {
        if (is_null($this->disk)){
            return 'unknown';
        }
        $adapterClass = get_class($this->disk->getDriver()->getAdapter());
        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower($filesystemType);
    }

    public static function create(string $diskname, string $backupName): self
    {
        try{
            $disk = app(Factory::class)->disk($diskname);
            return new static($disk, $backupName, $diskname);
        }catch (\Exception $exception){
            $backupDestination = new static(null,$backupName, $diskname);
            $backupDestination->connectionError = $exception;

            return $backupDestination;
        }
    }

    public function write(string $file)
    {
        if (is_null($this->disk)){
            throw InvalidBackupDestination::diskNotSet();
        }

        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);
        $handle = fopen($file, 'r+');
        $this->disk->getDriver()->writeStream($destination, $handle);
        if (is_resource($handle)){
            fclose($handle);
        }
    }

    public function backupName(): string
    {
        return $this->backupName;
    }

    public function backups(): BackupCollection
    {
        if ($this->backupCollectionCache){
            return $this->backupCollectionCache;
        }
        $files = is_null($this->disk) ? [] : $this->disk->allFiles($this->backupName);
        return $this->backupCollectionCache = BackupCollection::createFromFiles(
            $this->disk,$files
        );
    }

    public function connectionError(): Exception
    {
        return $this->connectionError;
    }

    public function isReachable(): bool
    {
        if (is_null($this->disk)){
            return false;
        }
        try{
            $this->disk->allFiles($this->backupName);
            return true;
        }catch (\Exception $exception){
            $this->connectionError = $exception;
            return false;
        }
    }
    public function usedStorage(): int
    {
        return $this->backups()->size();
    }

    public function newestBackup(): ?Backup
    {
        return $this->backups()->newest();
    }
    public function oldestBackup(): ?Backup
    {
        return $this->backups()->oldest();
    }
    public function newestBackupIsOlderThan(Carbon $date): bool
    {
        $newestBackup = $this->newestBackup();

        if (is_null($newestBackup)) {
            return true;
        }

        return $newestBackup->date()->gt($date);
    }

    public function fresh(): self
    {
        $this->backupCollectionCache = null;

        return $this;
    }
}