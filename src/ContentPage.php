<?php

namespace Gsons;

use Gsons\lib\Console;
use Gsons\lib\HttpCurl;
use Gsons\lib\Selector;
use think\Db;
use \Thread;

class ContentPage extends Thread
{
    /**
     * @var Spider;
     */
    private $spider;

    /**
     * @var
     */
    private $contentUrl;


    /**
     * ListPage constructor.
     * @param $spider
     * @param $contentUrl
     */
    public function __construct($spider, $contentUrl)
    {
        $this->spider = $spider;
        $this->contentUrl = $contentUrl;
        $this->spider->store->count++;
    }

    public function run()
    {
        $this->spider->loader->register();
        $curl = new HttpCurl([], false);
        $curl->setReferrer($this->contentUrl);
        $curl->get($this->contentUrl);
        $curl->close();
        $this->spider->store->count--;
        if ($curl->error) {
            $this->spider->store->contentCountFail++;
            Console::error("request content url {$this->contentUrl} failed,{$curl->error_message}");
            $this->spider->store->contentStack[]=$this->contentUrl;
            return false;
        } else {
            $this->spider->store->contentCount++;
            Console::log("request content url {$this->contentUrl} success");
        }

        $content = $curl->response;
        if ($this->spider->on_after_content) {
            $after_content = call_user_func($this->spider->on_after_content, $content);
            if (is_string($after_content)) $content = $after_content;
        }

        $data = [];
        foreach ($this->spider->config['field_arr'] as $vo) {
            $type = isset($vo['selector_type']) ? $vo['selector_type'] : 'xpath';
            $_temp = Selector::select($content, $vo['selector'], $type);
            if ($_temp === false) {
                $_temp = null;
            }

            $require = isset($vo['require']) ? $vo['require'] : false;
            if ($require && is_null($_temp)) {
                $data = false;
                break;
            }

            $repeated = isset($vo['repeated']) ? $vo['repeated'] : false;
            if ($repeated && is_string($_temp)) {
                $_temp = [$_temp];
            } else if (!$repeated && is_array($_temp)) {
                $_temp = $_temp[0];
            }
            $data[$vo['name']] = $_temp;
        }
        if ($this->spider->on_field) {
            $data=call_user_func($this->spider->on_field, $data,$this->contentUrl);
        }
        if($data) Db::table($this->spider->config['db']['table'])->insert($data);
    }

}