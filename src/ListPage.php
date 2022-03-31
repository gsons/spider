<?php

namespace Gsons;

use Gsons\lib\Console;
use Gsons\lib\HttpCurl;
use Gsons\lib\Selector;
use \Thread;

class ListPage extends Thread
{
    /**
     * @var Spider;
     */
    private $spider;

    /**
     * @var
     */
    private $listUrl;

    /**
     * ListPage constructor.
     * @param $spider
     * @param $listUrl
     */
    public function __construct($spider, $listUrl)
    {
        $this->spider = $spider;
        $this->listUrl = $listUrl;
        $this->spider->store->synchronized(function($store){$store->count++;}, $this->spider->store);
    }

    private function storeUrlArr()
    {
        $host = $this->spider->config['host'];
        $curl = new HttpCurl([], false);
        $curl->setReferrer($host);
        $curl->get($this->listUrl);
        $curl->close();
        $this->spider->store->synchronized(function($store){$store->count--;}, $this->spider->store);
        if ($curl->error) {
            $this->spider->store->listCountFail++;
            Console::error("request list url {$this->listUrl} failed,{$curl->error_message}");
            $this->spider->store->listStack[]=$this->listUrl;
            return false;
        } else {
            //$this->spider->store->listCount++;
            $this->spider->store->synchronized(function($store){$store->listCount++;}, $this->spider->store);
            Console::log("request list url {$this->listUrl} success");
        }
        $listContent = $curl->response;
        $listUrlArr = Selector::_regex_select($listContent, $this->spider->config['preg_list'], true);
        if (!empty($listUrlArr)&&is_array($listUrlArr)) {
            foreach ($listUrlArr as $url) {
                $url = strpos($url, $host) !== false ? $url : $host . $url;
                $this->spider->store->setList($url);
            }
        }
        $contentUrlArr = Selector::_regex_select($listContent, $this->spider->config['preg_content'], true);
        if (!empty($contentUrlArr)&&is_array($contentUrlArr)) {
            foreach ($contentUrlArr as $url) {
                $url = strpos($url, $host) !== false ? $url : $host . $url;
                $this->spider->store->setContent($url);
            }
        }
    }


    public function run()
    {
        $this->spider->loader->register();
        $this->storeUrlArr();
    }

}