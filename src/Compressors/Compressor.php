<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:02 PM
 */

namespace Kibb\Backup\Compressors;


interface Compressor
{

    public function useCommand(): string ;

    public function useExtension(): string ;

}