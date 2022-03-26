<?php

namespace Gsons;


class Store extends \Threaded
{

    public $listUrlArr = [];

    /**
     * @var \Volatile
     */
    public $listStack = [];


    public $contentUrlArr = [];

    public $listCount=0;

    public $contentCount=0;

    public $listCountFail=0;

    public $contentCountFail=0;

    //正在请求数
    public $count=0;



    /**
     * @var \Volatile
     */
    public $contentStack = [];

    public function countStack()
    {
        return $this->listStack->count() + $this->contentStack->count();
    }

    public function setList($url)
    {
        $key = urlencode($url);
        if (!isset($this->listUrlArr[$key])) {
            $this->listUrlArr[$key] = [
                'loaded' => false,
                'times' => 5,
                'url' => $url
            ];
            $this->listStack[] = $url;
        }
    }

    public function setContent($url)
    {
        $key = urlencode($url);
        if (!isset($this->contentUrlArr[$key])) {
            $this->contentUrlArr[$key] = [
                'loaded' => false,
                'times' => 3,
                'url' => $url
            ];
            $this->contentStack[] = $url;
        }
    }
}