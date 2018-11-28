<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 11/28/18
 * Time: 12:31 AM
 */

namespace Kibb\Backup\BackupDestination;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
class BackupCollection extends Collection
{
    /** @var null|int */
    protected $sizeCache = null;

    public static function createFromFiles(?Filesystem $disk, array $files): self
    {
        return (new static($files))
            ->filter(function ($path)use ($disk){
               return (new File)->isZipFile($disk, $path);
            })
            ->map(function ($path) use($disk){
                return new Backup($disk,$path);
            })
            ->sortBy(function (Backup $backup){
                return $backup->date()->timestamp;
            })
            ->values();
    }

    public function newest() : ?Backup
    {
        return $this->first();
    }

    public function oldest(): ?Backup
    {
        return $this->filter
            ->exists()
            ->latest();
    }

    public function size(): int
    {
        if ($this->sizeCache !== null){
            return $this->sizeCache;
        }

        return $this->sizeCache = $this->sum->size();
    }

}