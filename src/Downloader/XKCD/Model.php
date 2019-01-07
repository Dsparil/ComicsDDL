<?php
namespace App\Downloader\XKCD;

class Model
{
    private $month;

    private $num;

    private $link;

    private $year;

    private $news;

    private $safe_title;

    private $transcript;

    private $alt;

    private $img;

    private $title;

    private $day;

    public function getMonth()
    {
        return $this->month;
    }

    public function setMonth($month)
    {
        $this->month = $month;
    }

    public function getNum()
    {
        return $this->num;
    }

    public function setNum($num)
    {
        $this->num = $num;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;
    }

    public function getNews()
    {
        return $this->news;
    }

    public function setNews($news)
    {
        $this->news = $news;
    }

    public function getSafeTitle()
    {
        return $this->safe_title;
    }

    public function setSafeTitle($safe_title)
    {
        $this->safe_title = $safe_title;
    }

    public function getTranscript()
    {
        return $this->transcript;
    }

    public function setTranscript($transcript)
    {
        $this->transcript = $transcript;
    }

    public function getAlt()
    {
        return $this->alt;
    }

    public function setAlt($alt)
    {
        $this->alt = $alt;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function setImg($img)
    {
        $this->img = $img;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function setDay($day)
    {
        $this->day = $day;
    }
}
