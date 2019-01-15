<?php
namespace App\Downloader;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractDownloader implements DownloaderInterface
{
    /** @var array */
    protected $mimeTypes;

    /** @var Client */
    protected $client;

    /** @var OutputInterface */
    protected $output;

    /** @var Filesystem */
    protected $filesystem;

    protected $boundaryStart;

    protected $boundaryEnd;

    /**
     * @param Client          $client
     * @param OutputInterface $output
     * @param Filesystem      $filesystem
     * @param array           $mimeTypes
     */
    public function __construct(Client $client, OutputInterface $output, Filesystem $filesystem, array $mimeTypes)
    {
        $this->client     = $client;
        $this->output     = $output;
        $this->filesystem = $filesystem;
        $this->mimeTypes  = $mimeTypes;
    }

    /**
     * @param array $criteria
     * @param $url
     */
    protected function store(array $criteria, $url)
    {
        $this->formatFileName($criteria, $path, $fileName);

        $this->filesystem->mkdir($path, 0777);

        $imageString = file_get_contents($url);

        if (empty($imageString))
        {
            $this->output->writeln('<error>No data...</error>');
            return;
        }

        $fInfo = new \finfo(FILEINFO_MIME);
        $info  = $fInfo->buffer($imageString);
        $mime  = substr($info, 0, strpos($info, ';'));

        foreach ($this->mimeTypes as $mimeType => $extension)
        {
            if ($mime == $mimeType)
            {
                $fileName .= $extension;
                file_put_contents($path.$fileName, $imageString);
                return;
            }
        }

        $this->output->writeln('<error>MIME error.</error>');
        return;
    }

    /**
     * @param array $criteria
     * @return bool
     */
    protected function exists(array $criteria)
    {
        $this->formatFileName($criteria, $path, $fileName);

        foreach ($this->mimeTypes as $extension)
        {
            if ($this->filesystem->exists($path.$fileName.$extension))
            {
                return true;
            }
        }

        return false;
    }
}
