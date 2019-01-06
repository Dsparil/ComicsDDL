<?php
namespace App\Downloader;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractDownloader implements DownloaderInterface
{
    /** @var Client */
    protected $client;

    /** @var OutputInterface */
    protected $output;

    /** @var Filesystem */
    protected $filesystem;

    protected $boundaryStart;

    protected $boundaryEnd;

    /**
     * @param Client $client
     * @param OutputInterface $output
     * @param Filesystem $filesystem
     */
    public function __construct(Client $client, OutputInterface $output, Filesystem $filesystem)
    {
        $this->client     = $client;
        $this->output     = $output;
        $this->filesystem = $filesystem;
    }
}
