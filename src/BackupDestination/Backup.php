<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 11/28/18
 * Time: 12:31 AM
 */

namespace Kibb\Backup\BackupDestination;




use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;

class Backup
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $path;

    public function __construct(Filesystem $disk, string $path)
    {
     $this->disk = $disk;
     $this->path = $path;
    }


    public function path() : string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return $this->disk->exists($this->path);
    }

    public function date(): Carbon
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($this->path));
    }

    public function size(): int
    {
        if (!$this->exists()){
            return 0;
        }
        return $this->disk->size($this->path);
    }

    public function stream()
    {
        return $this->disk->readStream($this->path);
    }

    public function delete()
    {
        $this->disk->delete($this->path);
        consoleOutput()->info("Delete backup `{$this->path}`.");
    }

}