<?php
namespace App\Downloader\CommitStrip;

use App\Downloader\AbstractHtmlDownloader;
use GuzzleHttp\Exception\GuzzleException;

class Downloader extends AbstractHtmlDownloader
{
    const URL = 'http://www.commitstrip.com/fr/';

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

            $this->loadHtml($result->getBody());
            $elements = $this->xPathQuery("//div[@class='excerpt']/section/a");

            $elementsCount = $elements->length;

            for ($idx = 0 ; $idx < $elements->length ; $idx++)
            {
                $element = $elements->item($idx);
                try
                {
                    $link = $element->getAttribute('href');

                    $slugAndDate = substr($link, strlen(self::URL));

                    preg_match_all('/^([\d]{4}\/[\d]{2}\/[\d]{2})\/([A-Za-z0-9\-\_]+)\/$/', $slugAndDate, $matches);

                    $slug = $matches[2][0];
                    $date = str_replace('/', '-', $matches[1][0]);

                    $this->stripList[$slug] = [
                        'date' => $date,
                        'url' => $link
                    ];
                }
                catch (\Exception $e)
                {
                    $this->output->write('No strip found...', true);
                }
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
            $elements = $this->xPathQuery("//img[contains(@class, 'wp-image')]");

            if ($elements->length > 0)
            {
                $this->store($criteria, $elements->item(0)->getAttribute('src'));
                $this->output->write(' OK.', true);
            }
            else
            {
                $this->output->write(' No image found, aborting...', true);
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

        $path     = 'downloaded/commitstrip/'.$year.'/';
        $fileName = $dateStr.' - '.$criteria['slug'];
    }
}
