<?php
namespace App\Downloader\XKCD;

use App\Downloader\AbstractDownloader;
use App\Downloader\Traits\DateBoundariesTrait;
use App\Downloader\Traits\NumberBoundariesTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

class Downloader extends AbstractDownloader
{
    use NumberBoundariesTrait;

    const URL_LAST = 'http://xkcd.com/info.0.json';

    const URL_MASK = 'http://xkcd.com/%d/info.0.json';

    const OLDEST_NUMBER = 1;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @param Client $client
     * @param OutputInterface $output
     * @param Filesystem $filesystem
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

    public function setBoundaries($boundaryStart, $boundaryEnd)
    {
        if ($this->boundaryStart === null)
        {
            $this->boundaryStart = self::OLDEST_NUMBER;
        }

        if ($this->boundaryEnd === null)
        {
            $this->boundaryEnd = $this->getNewestNumber();
        }

        $this->convertAndCheckNumbers($boundaryStart, $boundaryEnd);
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $numberStart = $this->boundaryStart;
        $numberEnd   = $this->boundaryEnd;

        do
        {
            $this->getStripForNumber($numberStart);
            $numberStart++;
        }
        while ($numberStart <= $numberEnd);
    }

    /**
     * @param int $number
     */
    private function getStripForNumber($number)
    {
        $this->output->write('Processing XKCD n°'.$number.'... ');

        try
        {
            $url    = sprintf(self::URL_MASK, $number);
            $result = $this->client->request('GET', $url);

            /** @var Model $response */
            $response = $this->serializer->deserialize($result->getBody(), Model::class, 'json');

            $criteria = [
                'year'   => (int) $response->getYear(),
                'title'  => $response->getSafeTitle(),
                'number' => $number
            ];

            if ($this->exists($criteria))
            {
                $this->output->write('Skipped.', true);
                return;
            }

            $this->store($criteria, $response->getImg());

            $this->output->write('OK.', true);
        }
        catch (GuzzleException $e)
        {
            echo $e->getMessage();
            $this->output->write('Request error ('.$e->getMessage().')', true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function formatFileName(array $criteria, &$path = '', &$fileName = '')
    {
        $path     = 'downloaded/xkcd/'.$criteria['year'].'/';
        $fileName = $criteria['number'].'-'.$criteria['title'].'.png';
    }

    /**
     * @return int
     */
    private function getNewestNumber()
    {
        try
        {
            $result = $this->client->request('GET', self::URL_LAST);

            /** @var Model $response */
            $response = $this->serializer->deserialize($result->getBody(), Model::class, 'json');

            return (int) $response->getNum();
        }
        catch (GuzzleException $e)
        {
            echo $e->getMessage();
            $this->output->write('Request error ('.$e->getMessage().')', true);
        }
    }
}
