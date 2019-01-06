<?php
namespace App\Downloader;

interface DownloaderInterface
{
    /**
     * @param $boundaryStart
     * @param $boundaryEnd
     */
    function setBoundaries($boundaryStart, $boundaryEnd);

    function process();
}
