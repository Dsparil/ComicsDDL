<?php
namespace App\Downloader;

class DownloaderNotFoundException extends \InvalidArgumentException
{
    public function __construct($downloaderName)
    {
        $message = sprintf('Downloader for %s is not implemented.', $downloaderName);
        parent::__construct($message);
    }
}
