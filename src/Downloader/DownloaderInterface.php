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

    /**
     * @param array $criteria
     * @param string $path
     * @param string $fileName
     */
    function formatFileName(array $criteria, &$path = '', &$fileName = '');
}
