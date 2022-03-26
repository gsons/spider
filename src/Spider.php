<?php

namespace Gsons;

use Composer\Autoload\ClassLoader;
use Gsons\lib\Console;
use Gsons\lib\Date;
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
    public $start_date;

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
        $this->start_date=date('Y-m-d H:i:s');
    }

    private function initList()
    {
        foreach ($this->config['list_url'] as $url) {
            $this->store->listStack[] = $url;
        }
    }

    public static function memory_get_usage()
    {
        $memory = memory_get_usage();
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2) . ' ' . $unit[$i];
    }

    public function showUi()
    {
        static $flag = false;
        $version = PHP_VERSION;
        if ($flag) echo "\033[7A"; else $flag = true;
        $date_time=date('Y-m-d H:i:s');
        $run_time=Date::time2second(strtotime($date_time)-strtotime($this->start_date));
        $mem=self::memory_get_usage();
        $pid=getmypid();
        $ui = <<<UI

\033[1;37m=============================Thread Spider=============================
\033[1;37mapp version:\033[1;32m0.0.1  \033[1;37mphp version:\033[1;32m{$version}  \033[1;37mnow time:\033[1;32m{$date_time}
\033[1;37mstart time:\033[1;32m{$this->start_date}  \033[1;37mrun:\033[1;32m{$run_time}
\033[1;37mrequesting:\033[1;32m{$this->store->count} 
\033[1;37mlist page:\033[1;32m{$this->store->listCount}/\033[1;31m{$this->store->listCountFail}  \033[1;37mcontent page:\033[1;32m{$this->store->contentCount}/\033[1;31m{$this->store->contentCountFail}
\033[1;37mmemory:\033[1;32m{$mem}  \033[1;37mprocess id:\033[1;32m{$pid}  
\033[1;37m=======================================================================
UI;
        echo $ui;
        echo "\033[?25l";
    }


    public function exec(){
        $this->start();
        while ($this->isRunning()){
            usleep(30);
            $this->showUi();
        }
        echo PHP_EOL.'application  exit!'.PHP_EOL;
    }

    public function run()
    {
        $this->loader->register();
        $this->initList();
        $max_request = $this->config['max_request'];
        $thread_list=[];
        while ($this->store->countStack() || $this->store->count>0) {

            //当每秒请求数过大时暂缓执行
            if ($this->store->count > $max_request) {
                usleep(100);
                continue;
            }

            //当内容页少于并发数时 再请求列表页
            for ($i = 0; $i < $max_request && $this->store->contentStack->count() < $max_request; $i++) {
                $listUrl = $this->store->listStack->shift();
                if ($listUrl) {
                    $key=uniqid().urlencode($listUrl);
                    $thread_list[$key]=new ListPage($this, $listUrl);
                    $thread_list[$key]->start();
                } else {
                    break;
                }
            }

            for ($j = $max_request - $i; $j > 0; $j--) {
                $contentUrl = $this->store->contentStack->shift();
                if ($contentUrl) {
                    $key=uniqid().urlencode($contentUrl);
                    $thread_list[$key]=new ContentPage($this, $contentUrl);
                    $thread_list[$key]->start();
                } else {
                    break;
                }
            }
            foreach ($thread_list as &$thread){
                if($thread->isTerminated()){
                    $id=$thread->getCurrentThreadId();
                    Console::error("thread $id is Terminated");
                }
                if(!$thread->isRunning()){
                    unset($thread);
                }
            }
            sleep(1);
        }
    }


}