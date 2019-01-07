<?php
namespace App\Downloader;

interface DownloaderInterface
{
    const DOWNLOADER_TYPE_NONE = 0;

    const DOWNLOADER_TYPE_HTML = 1;

    const DOWNLOADER_TYPE_JSON_API = 2;

    const DOWNLOADER_TYPE = self::DOWNLOADER_TYPE_NONE;

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
