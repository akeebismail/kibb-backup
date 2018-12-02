<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 9:00 AM
 */

namespace Kibb\Backup\Helpers;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
class File
{
    /** @var array */
    protected static $allowedMimeTypes = [
      'application/zip',
      'application/x-zip',
      'application/x-gzip'
    ];

    public function isZipFile(?Filesystem $disk, string $path)
    {
        if ($this->hasZipExtension($path))
        {
            return true;
        }
        return $this->hasAllowedMimeType($disk, $path);
    }

    public function hasZipExtension(string $path): bool
    {
        return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
    }

    public function hasAllowedMimeType(?Filesystem $disk, string $path)
    {
        return in_array($this->mimeType($disk,$path), self::$allowedMimeTypes);
    }

    public function mimeType(?Filesystem $disk, string $path)
    {
        try{
            if ($disk && method_exists($disk,'mimeType')){
                return $disk->mimeType($path);
            }
        }catch (Exception $exception){

        }
        return false;
    }
}