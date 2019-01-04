<?php
namespace App\Downloader\Dilbert;

use App\Downloader\AbstractDownloader;
use GuzzleHttp\Exception\GuzzleException;

class Downloader extends AbstractDownloader
{
    const URL = 'https://dilbert.com/strip';

    const OLDEST_DATE = '1989-04-16';

    /**
     * {@inheritdoc}
     */
    public function process(\DateTimeInterface $dateBegin = null, \DateTimeInterface $dateEnd = null)
    {
        if ($dateEnd === null)
        {
            $dateEnd = new \DateTime();
        }

        if ($dateBegin === null)
        {
            $dateBegin = new \DateTime(self::OLDEST_DATE);
        }

        $step = new \DateInterval('P1D');

        do
        {
            $this->getStripForDate($dateBegin);
            $dateBegin->add($step);
        }
        while ($dateBegin <= $dateEnd);
    }

    /**
     * @param \DateTimeInterface $date
     */
    private function getStripForDate(\DateTimeInterface $date)
    {
        $this->output->write('Processing date '.$date->format('Y-m-d').'... ');

        try
        {
            $url    = sprintf('%s/%s', self::URL, $date->format('Y-m-d'));
            $result = $this->client->request('GET', $url);

            $html = $result->getBody();

            $hDom = new \DOMDocument();
            // Mute errors while parsing clumsy (but still exploitable) HTML.
            @$hDom->loadHTML($html);
            $hXpath   = new \DOMXPath($hDom);
            $elements = $hXpath->query("//img[contains(@class, 'img-comic')]");

            if ($elements->length == 1)
            {
                $element = $elements->item(0);
                $imgSrc  = $element->getAttribute('src');
                $this->store('https:'.$imgSrc, $date);
                $this->output->write('OK.', true);
            }
            else
            {
                $this->output->write('Strip not found.', true);
            }
        }
        catch (GuzzleException $e)
        {
            echo $e->getMessage();
            $this->output->write('Request error ('.$e->getMessage().')', true);
        }
    }

    /**
     * @param $imageUrl
     * @param \DateTimeInterface $date
     */
    private function store($imageUrl, \DateTimeInterface $date)
    {
        $day  = $date->format('N');
        $year = $date->format('Y');

        switch ($day)
        {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                $category = 'Story';
                break;
            case 6:
                $category = 'SaturdaySpecial';
                break;
            case 7:
                $category = 'SundaySpecial';
                break;
            default:
                $category = 'Undefined';
        }

        $path     = 'downloaded/dilbert/'.$category.'/'.$year.'/';
        $fileName = $date->format('Y-m-d').'.gif';

        $this->filesystem->mkdir($path, 0777);

        $imageString = file_get_contents($imageUrl);
        file_put_contents($path.$fileName, $imageString);
    }
}
