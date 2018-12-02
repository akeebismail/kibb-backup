<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 12/2/18
 * Time: 1:03 PM
 */

namespace Kibb\Backup\Compressors;


class GzipCompressor implements Compressor
{

    public function useCommand(): string
    {

        return 'gzip';
    }

    public function useExtension(): string
    {
        return 'gz';
    }
}