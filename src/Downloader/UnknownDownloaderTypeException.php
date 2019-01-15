<?php
namespace App\Downloader;

class UnknownDownloaderTypeException extends \InvalidArgumentException
{
    public function __construct($className)
    {
        $message = sprintf('Bad downloader type for %s.', $className);
        parent::__construct($message);
    }
}
