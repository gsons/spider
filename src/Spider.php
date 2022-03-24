<?php

namespace Gsons;

use Composer\Autoload\ClassLoader;
use Gsons\lib\Console;
use think\Db;
use \Worker;
use \Pool;

class Spider extends Worker
{
    public $loader;
    public $config;
    public $store;
    public $on_after_content;
    public $on_field;
    public $db;

    /**
     * Spider constructor.
     * @param $config array
     * @param $loader ClassLoader
     */
    public function __construct($config, $loader)
    {
        $this->config = $config;
        $this->loader = $loader;
        $this->store = new Store();
        Db::setConfig($config['db']);
    }

    private function initList()
    {
        foreach ($this->config['list_url'] as $url) {
            $this->store->listStack[] = $url;
        }
    }

    public function run()
    {
        $this->loader->register();
        $this->initList();
        $max_request = $this->config['max_request'];
        $pool = new Pool($max_request);
        while ($this->store->countStack()) {
            if($this->store->count>$max_request){
                Console::log("正在请求数量：{$this->store->count}");
                sleep(1);
                continue;
            }

            for ($i = 0; $i < $max_request / 3 + 1; $i++) {
                $listUrl = $this->store->listStack->shift();
                if ($listUrl) {
                    $pool->submit(new ListPage($this, $listUrl));
                } else {
                    break;
                }
            }

            for ($j = $max_request - $i; $j > 0; $j--) {
                $contentUrl = $this->store->contentStack->shift();
                if ($contentUrl) {
                    $pool->submit(new ContentPage($this, $contentUrl));
                } else {
                    break;
                }
            }

            Console::log("请求数量：{$this->store->count}");
            sleep(1);
        }
    }

}