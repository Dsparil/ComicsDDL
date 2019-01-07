<?php
namespace App\Downloader;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractJsonAPIDownloader extends AbstractDownloader
{
    const DOWNLOADER_TYPE = self::DOWNLOADER_TYPE_JSON_API;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * {@inheritdoc}
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Client $client,
        OutputInterface $output,
        Filesystem $filesystem,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        parent::__construct($client, $output, $filesystem);
    }
}
