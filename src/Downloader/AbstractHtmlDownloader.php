<?php
namespace App\Downloader;

use GuzzleHttp\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractHtmlDownloader extends AbstractDownloader
{
    const DOWNLOADER_TYPE = self::DOWNLOADER_TYPE_HTML;

    /** @var \DOMDocument */
    protected $domDocument;

    /**
     * {@inheritdoc}
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Client $client,
        OutputInterface $output,
        Filesystem $filesystem,
        \DOMDocument $domDocument
    ) {
        $this->domDocument = $domDocument;
        parent::__construct($client, $output, $filesystem);
    }

    /**
     * @param string $html
     */
    protected function loadHtml($html)
    {
        // Mute errors while parsing clumsy (but still exploitable) HTML.
        @$this->domDocument->loadHTML($html);
    }

    /**
     * @param string $query
     * @return \DOMNodeList
     */
    protected function xPathQuery($query)
    {
        $xPath = new \DOMXPath($this->domDocument);
        return $xPath->query($query);
    }
}
