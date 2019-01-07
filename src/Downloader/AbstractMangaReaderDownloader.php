<?php
namespace App\Downloader;

use GuzzleHttp\Exception\GuzzleException;

abstract class AbstractMangaReaderDownloader extends AbstractHtmlDownloader
{
    const URL_BASE = 'https://www.mangareader.net/';

    /** @var string */
    protected $mangaName = '';

    /**
     * {@inheritdoc}
     */
    public function setBoundaries($boundaryStart, $boundaryEnd)
    {
        return true;
    }

    /**
     * @param string $mangaName
     */
    protected function setMangaName($mangaName)
    {
        $this->mangaName = $mangaName;
    }

    public function process()
    {
        $this->getPage(self::URL_BASE.$this->mangaName.'/1');
    }

    /**
     * @param string $url
     */
    private function getPage($url)
    {
        preg_match('/\/([0-9]+)\/{0,1}([0-9]*)$/', $url, $matches);

        $chapter = $matches[1];
        $page    = (isset($matches[2]) && !empty($matches[2]))? $matches[2] : '1';
        $nextUrl = '';

        $this->output->write('Processing chapter '.$chapter.', page '.$page.'... ');

        try
        {
            $result = $this->client->request('GET', $url);

            $this->loadHtml($result->getBody());
            $nextLinkList = $this->xPathQuery('//span[@class="next"]/a');
            $imgList      = $this->xPathQuery('//img[@id="img"]');

            if ($imgList->length > 0)
            {
                $imgUrl   = $imgList->item(0)->getAttribute('src');
                $criteria = [
                    'chapter' => $chapter,
                    'page'    => $page,
                    'url'     => $imgUrl
                ];
                if (!$this->exists($criteria))
                {
                    $this->store($criteria, $imgUrl);
                    $this->output->writeln('OK.');
                }
                else
                {
                    $this->output->writeln('Skipped !');
                }
            }
            else
            {
                $this->output->writeln('No image found.');
            }

            if ($nextLinkList->length > 0)
            {
                $nextUrl = self::URL_BASE.substr($nextLinkList->item(0)->getAttribute('href'), 1);
            }
        }
        catch (GuzzleException $e)
        {
            $this->output->write('Request error ('.$e->getMessage().')', true);
        }

        if (!empty($nextUrl))
        {
            $this->getPage($nextUrl);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function formatFileName(array $criteria, &$path = '', &$fileName = '')
    {
        $chapter  = str_pad($criteria['chapter'], 4, '0', STR_PAD_LEFT);
        $page     = str_pad($criteria['page'],    3, '0', STR_PAD_LEFT);
        $ext      = pathinfo($criteria['url'], PATHINFO_EXTENSION);

        $path     = 'downloaded/'.$this->mangaName.'/Chapter '.$chapter.'/';
        $fileName = $this->mangaName.' - '.$chapter.' - '.$page.'.'.$ext;
    }
}
