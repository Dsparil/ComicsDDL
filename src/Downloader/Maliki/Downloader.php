<?php
namespace App\Downloader\Maliki;

use App\Downloader\AbstractDownloader;
use App\Downloader\Traits\DateBoundariesTrait;
use GuzzleHttp\Exception\GuzzleException;

class Downloader extends AbstractDownloader
{
    const URL = 'https://maliki.com/strips/';

    /** @var array */
    private $stripList = [];

    // No boundaries on this comic strip.
    public function setBoundaries($boundaryStart, $boundaryEnd)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $this->getAvailableStrips();

        foreach ($this->stripList as $slug => $stripInfo)
        {
            $this->getStrip($slug, $stripInfo);
        }
    }

    /**
     * @param int $page
     */
    private function getAvailableStrips($page = 1)
    {
        $this->output->write('Getting available strips on page '.$page.'... ');

        $elementsCount = 0;
        try
        {
            $result = $this->client->request('GET', sprintf('%spage/%d', self::URL, $page));
            $html   = $result->getBody();

            $hDom = new \DOMDocument();
            // Mute errors while parsing clumsy (but still exploitable) HTML.
            @$hDom->loadHTML($html);
            $hXpath   = new \DOMXPath($hDom);
            $elements = $hXpath->query("//div[@class='stripItem']");

            $elementsCount = $elements->length;

            for ($idx = 0 ; $idx < $elements->length ; $idx++)
            {
                $element = $elements->item($idx);
                $link    = $element->parentNode->getAttribute('href');
                try
                {
                    $date = $element
                        ->childNodes
                        ->item(3)
                        ->getElementsByTagName('time')
                        ->item(0)
                        ->getAttribute('datetime')
                    ;
                }
                catch(\Exception $e)
                {
                    $date = '';
                }

                $slug = substr($link, strrpos($link, '/', -2) + 1, -1);

                $this->stripList[$slug] = [
                    'date' => $date,
                    'url'  => $link
                ];
            }

            $this->output->write($elementsCount.' strip(s) found.', true);
        }
        catch (GuzzleException $e)
        {
            echo $e->getMessage();
            $this->output->write('Request error ('.$e->getMessage().')', true);
        }

        if ($elementsCount > 0)
        {
            $this->getAvailableStrips(++$page);
        }
    }

    /**
     * @param string $slug
     * @param array  $stripInfo
     */
    private function getStrip($slug, array $stripInfo)
    {
        $this->output->write('Processing strip '.$slug.'...');

        if ($this->exists($stripInfo['date'], $slug))
        {
            $this->output->write('Skipped !', true);
            return;
        }

        try
        {
            $result = $this->client->request('GET', $stripInfo['url']);

            $html = $result->getBody();

            $hDom = new \DOMDocument();
            // Mute errors while parsing clumsy (but still exploitable) HTML.
            @$hDom->loadHTML($html);
            $hXpath   = new \DOMXPath($hDom);
            $elements = $hXpath->query("//img[@class='center-block img-responsive']");

            if ($elements->length == 1)
            {
                $this->store($stripInfo['date'], $slug, $elements->item(0)->getAttribute('src'));
                $this->output->write(' Single image strip.', true);
            }
            elseif ($elements->length > 1)
            {
                for ($idx = 0 ; $idx < $elements->length ; $idx++)
                {
                    $mgSrc = $elements->item($idx)->getAttribute('src');
                    $this->store($stripInfo['date'], $slug, $mgSrc, $idx + 1);
                    $this->output->write('.');
                }
                $this->output->write(' Multi-image strip.', true);
            }
            else
            {
                $this->output->write(' Strip not found.', true);
            }
        }
        catch (GuzzleException $e)
        {
            echo $e->getMessage();
            $this->output->write(' Request error ('.$e->getMessage().')', true);
        }
    }

    /**
     * @param $date
     * @param $slug
     * @param string $path
     * @param string $fileName
     * @param int|null $imageNumber
     */
    private function formatFileName($date, $slug, &$path = '', &$fileName = '', $imageNumber = null)
    {
        if (!empty($date))
        {
            $stripDate = new \DateTime($date);
            $year      = $stripDate->format('Y');
            $dateStr   = $stripDate->format('Y-m-d');
        }
        else
        {
            $year    = 'undefined';
            $dateStr = 'undefined';
        }

        $path = 'downloaded/maliki/'.$year.'/';

        if ($imageNumber !== null)
        {
            $path    .= $dateStr.' - '.$slug.'/';
            $fileName = $dateStr.' - '.$slug.' - '.$imageNumber.'.jpg';
        }
        else
        {
            $fileName = $dateStr.' - '.$slug.'.jpg';
        }
    }

    /**
     * @param $date
     * @param $slug
     * @param $imageUrl
     * @param null $imageNumber
     */
    private function store($date, $slug, $imageUrl, $imageNumber = null)
    {
        $this->formatFileName($date, $slug, $path, $fileName, $imageNumber);

        $this->filesystem->mkdir($path, 0777);

        $imageString = file_get_contents($imageUrl);
        file_put_contents($path.$fileName, $imageString);
    }

    /**
     * @param $date
     * @param $slug
     * @return bool
     */
    private function exists($date, $slug)
    {
        $this->formatFileName($date, $slug, $path, $fileName);

        if ($this->filesystem->exists($path.$fileName))
        {
            return true;
        }

        $this->formatFileName($date, $slug, $path, $fileName, 1);

        return $this->filesystem->exists($path.$fileName);
    }
}
