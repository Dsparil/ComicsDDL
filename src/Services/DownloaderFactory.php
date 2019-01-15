<?php
namespace App\Services;

use App\Downloader\AbstractDownloader;
use App\Downloader\DownloaderInterface;
use App\Downloader\DownloaderNotFoundException;
use App\Downloader\UnknownDownloaderTypeException;
use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DownloaderFactory
{
    /** @var array */
    private $downloaderList;

    /** @var array */
    private $mimeTypeList;

    /**
     * @param array $downloaderList
     * @param array $mimeTypeList
     */
    public function __construct(array $downloaderList, array $mimeTypeList)
    {
        $this->downloaderList = $downloaderList;
        $this->mimeTypeList   = $mimeTypeList;
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

        /** @var DownloaderInterface $class */
        $class = $this->downloaderList[$downloaderName];

        switch ($class::DOWNLOADER_TYPE)
        {
            case DownloaderInterface::DOWNLOADER_TYPE_NONE:
                return new $class(
                    new Client(),
                    $output,
                    new Filesystem(),
                    $this->mimeTypeList
                );
                break;

            case DownloaderInterface::DOWNLOADER_TYPE_HTML:
                return new $class(
                    new Client(),
                    $output,
                    new Filesystem(),
                    $this->mimeTypeList,
                    new \DOMDocument()
                );
                break;

            case DownloaderInterface::DOWNLOADER_TYPE_JSON_API:
                return new $class(
                    new Client(),
                    $output,
                    new Filesystem(),
                    $this->mimeTypeList,
                    new Serializer([new ObjectNormalizer()], [new JsonDecode(), new JsonEncoder()])
                );
                break;

            default:
                throw new UnknownDownloaderTypeException(sprintf('Bad downloader type for %s.', $class));
        }
    }
}
