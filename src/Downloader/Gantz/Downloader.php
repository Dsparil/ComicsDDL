<?php
namespace App\Downloader\Gantz;

use App\Downloader\AbstractMangaReaderDownloader;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Downloader extends AbstractMangaReaderDownloader
{
    public function __construct(
        Client $client,
        OutputInterface $output,
        Filesystem $filesystem,
        \DOMDocument $domDocument
    ) {
        parent::__construct($client, $output, $filesystem, $domDocument);
        $this->setMangaName('gantz');
    }
}
