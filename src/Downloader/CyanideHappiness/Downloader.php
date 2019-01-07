<?php
namespace App\Downloader\CyanideHappiness;

use App\Downloader\AbstractHtmlDownloader;
use GuzzleHttp\Exception\GuzzleException;

class Downloader extends AbstractHtmlDownloader
{
    const URL = 'http://explosm.net/';

    const OLDEST_DATE = '1989-04-16';

    public function setBoundaries($boundaryStart, $boundaryEnd)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $this->getStrip(self::URL);
    }

    /**
     * @param string $url
     */
    private function getStrip($url)
    {
        $this->output->write('Processing strip... ');
        $prevLink = '';

        try
        {
            $result = $this->client->request('GET', $url);

            $this->loadHtml($result->getBody());
            $imageList      = $this->xPathQuery("//img[@id='main-comic']");
            $prevLinkList   = $this->xPathQuery("//a[contains(@class, 'nav-previous')]");
            $commentBtnList = $this->xPathQuery("//button[@id='comic-comment-button']");
            $authorList     = $this->xPathQuery("//div[@id='comic-author']");

            // Get date
            $dateText = $authorList->item(0)->childNodes->item(0)->wholeText;
            preg_match('/([\d]{4}\.[\d]{2}\.[\d]{2})/', $dateText, $matches);
            $dateStr = str_replace('.', '-', $matches[1]);

            // Get comic number
            $slug = $commentBtnList->item(0)->getAttribute('data-slug');
            $comicNumber = substr($slug, strpos($slug, '-') + 1);

            $this->output->write('Found number '.$comicNumber.'... ');

            $criteria = [
                'date'   => $dateStr,
                'number' => $comicNumber
            ];

            if ($this->exists($criteria))
            {
                $this->output->writeln('Skipped !');
            }
            elseif ($imageList->length == 1)
            {
                $image = $imageList->item(0);
                $this->store($criteria, 'http:'.$image->getAttribute('src'));
                $this->output->writeln('OK.');
            }
            else
            {
                $this->output->writeln('Strip not found.');
            }

            if ($prevLinkList->length > 0)
            {
                $prevLink = self::URL.$prevLinkList->item(0)->getAttribute('href');
            }
        }
        catch (GuzzleException $e)
        {
            echo $e->getMessage();
            $this->output->write('Request error ('.$e->getMessage().')', true);
        }

        if (!empty($prevLink))
        {
            $this->getStrip($prevLink);
        }
    }

    /**
     * {@inheritdoc}
     * @throws \UnexpectedValueException
     */
    public function formatFileName(array $criteria, &$path = '', &$fileName = '')
    {
        $date = $criteria['date'];

        if (empty($date))
        {
            $year = 'undefined';
        }
        else
        {
            $date = new \DateTime($date);
            $year = $date->format('Y');
        }

        $path     = 'downloaded/cyanide-happiness/'.$year.'/';
        $fileName = $criteria['number'].'.png';
    }
}
