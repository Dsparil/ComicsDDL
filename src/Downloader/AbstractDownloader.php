<?php
namespace App\Downloader;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractDownloader
{
    /** @var Client */
    protected $client;

    /** @var OutputInterface */
    protected $output;

    /** @var Filesystem */
    protected $filesystem;

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

    /**
     * @param \DateTimeInterface|null $dateBegin
     * @param \DateTimeInterface|null $dateEnd
     */
    abstract public function process(\DateTimeInterface $dateBegin = null, \DateTimeInterface $dateEnd = null);
}
