<?php
namespace App\Downloader\Maliki;

use App\Downloader\AbstractHtmlDownloader;
use GuzzleHttp\Exception\GuzzleException;

class Downloader extends AbstractHtmlDownloader
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

            // Mute errors while parsing clumsy (but still exploitable) HTML.
            $this->loadHtml($result->getBody());
            $elements = $this->xPathQuery("//div[@class='stripItem']");

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

        $criteria = [
            'date' => $stripInfo['date'],
            'slug' => $slug
        ];

        if ($this->exists($criteria))
        {
            $this->output->write('Skipped !', true);
            return;
        }

        try
        {
            $result = $this->client->request('GET', $stripInfo['url']);

            $this->loadHtml($result->getBody());
            $elements = $this->xPathQuery("//img[@class='center-block img-responsive']");

            if ($elements->length == 1)
            {
                $this->store($criteria, $elements->item(0)->getAttribute('src'));
                $this->output->write(' Single image strip.', true);
            }
            elseif ($elements->length > 1)
            {
                for ($idx = 0 ; $idx < $elements->length ; $idx++)
                {
                    $criteria['imageNumber'] = $idx + 1;
                    $imgSrc = $elements->item($idx)->getAttribute('src');
                    $this->store($criteria, $imgSrc);
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
     * {@inheritdoc}
     */
    public function formatFileName(array $criteria, &$path = '', &$fileName = '')
    {
        if (!empty($criteria['date']))
        {
            $stripDate = new \DateTime($criteria['date']);
            $year      = $stripDate->format('Y');
            $dateStr   = $stripDate->format('Y-m-d');
        }
        else
        {
            $year    = 'undefined';
            $dateStr = 'undefined';
        }

        $path = 'downloaded/maliki/'.$year.'/';

        if (array_key_exists('imageNumber', $criteria))
        {
            $path    .= $dateStr.' - '.$criteria['slug'].'/';
            $fileName = $dateStr.' - '.$criteria['slug'].' - '.$criteria['imageNumber'];
        }
        else
        {
            $fileName = $dateStr.' - '.$criteria['slug'];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function exists(array $criteria)
    {
        if (parent::exists($criteria))
        {
            return true;
        }

        $criteria['imageNumber'] = 1;

        return parent::exists($criteria);
    }
}
