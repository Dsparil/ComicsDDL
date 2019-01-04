<?php
namespace App\Downloader\Services;

use App\Downloader\AbstractDownloader;
use App\Downloader\DownloaderNotFoundException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Factory
{
    /** @var array */
    private $downloaderList;

    /**
     * @param array $downloaderList
     */
    public function __construct(array $downloaderList)
    {
        $this->downloaderList = $downloaderList;
    }

    /**
     * @param string $downloaderName
     * @param OutputInterface $output
     * @return AbstractDownloader
     * @throws DownloaderNotFoundException
     */
    public function create($downloaderName, OutputInterface $output)
    {
        if (!array_key_exists($downloaderName, $this->downloaderList))
        {
            throw new DownloaderNotFoundException(sprintf('Downloader for %s is not implemented.', $downloaderName));
        }

        $class = $this->downloaderList[$downloaderName];

        return new $class(new Client(), $output, new Filesystem());
    }
}
