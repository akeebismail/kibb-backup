<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 1/27/19
 * Time: 10:29 PM
 */

namespace Kibb\Backup\Events;


use Kibb\Backup\Tasks\Backup\Manifest;

class BackupManifestWasCreated
{

    /** @var \Kibb\Backup\Tasks\Backup\Manifest */
    public $manifest;

    public function __construct(Manifest $manifest)
    {
        $this->manifest = $manifest;
    }
}