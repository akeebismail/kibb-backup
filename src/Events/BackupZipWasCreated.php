<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/27/19
 * Time: 10:32 PM
 */

namespace Kibb\Backup\Events;


class BackupZipWasCreated
{
    /** @var string */
    public $pathToZip;

    public function __construct(string $pathToZip)
    {
        $this->pathToZip = $pathToZip;
    }

}