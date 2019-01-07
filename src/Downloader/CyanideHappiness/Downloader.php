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
            $imageList   = $this->xPathQuery("//img[@id='main-comic']");
            $stripNumber = $this->getStripNumber();

            $this->output->write('Found number '.$stripNumber.'... ');

            $criteria = [
                'date'   => $this->getDate(),
                'number' => $stripNumber
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

            $prevLink = $this->getPreviousLink();
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
     * @return string
     */
    private function getDate()
    {
        // Search "author" div where there is current strip's date.
        $authorList = $this->xPathQuery("//div[@id='comic-author']");

        if ($authorList->length == 0)
        {
            throw new \UnexpectedValueException('Unable to find author DIV.');
        }

        // This div's first child contains the date with additional characters.
        try
        {
            $dateText = $authorList->item(0)->childNodes->item(0)->wholeText;
            preg_match('/([\d]{4}\.[\d]{2}\.[\d]{2})/', $dateText, $matches);
            return str_replace('.', '-', $matches[1]);
        }
        catch (\Exception $e)
        {
            return '';
        }
    }

    /**
     * @return string
     */
    private function getStripNumber()
    {
        $commentBtnList = $this->xPathQuery("//button[@id='comic-comment-button']");

        if ($commentBtnList->length == 0)
        {
            throw new \UnexpectedValueException('Unable to find strip number.');
        }

        $slug = $commentBtnList->item(0)->getAttribute('data-slug');
        return substr($slug, strpos($slug, '-') + 1);
    }

    /**
     * @return string
     */
    private function getPreviousLink()
    {
        $prevLink     = '';
        $prevLinkList = $this->xPathQuery("//a[contains(@class, 'nav-previous')]");

        if ($prevLinkList->length > 0)
        {
            $prevLink = self::URL.$prevLinkList->item(0)->getAttribute('href');
        }

        return $prevLink;
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
