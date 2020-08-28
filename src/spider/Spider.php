<?php
/**
 * Created by PhpStorm.
 * User: gsonhub
 * Date: 2020-08-27
 * Time: 0:04
 */

namespace Gsons\spider;

use Gsons\HttpCurl;
use Composer\Autoload\ClassLoader;
use think\Cache;
use think\Db;
use Gsons\Console;

class Spider
{
    /**
     * 默认列表页最大次数
     */
    static $MAX_LIST_TIME = 5;

    /**
     * 默认内容页最大次数
     */
    static $MAX_CONTENT_TIME = 3;

    static $MAX_REQUEST = 10;

    static $SLEEP_TIME = 100;

    /**
     * 待爬列表页
     * @var array
     */
    static $listUrlList = [];

    /**
     * 已爬列表页
     * @var array
     */
    static $_listUrlList = [];

    /**
     * 待爬内容页
     * @var array
     */
    static $contentUrlArr = [];

    /**
     * 已爬内容页
     * @var array
     */
    static $_contentUrlArr = [];

    /**
     * 记录每个url的剩余请求个数
     * @var array
     */
    static $urlTimesObj = [];

    /**
     * @param $config
     */
    static function init($config)
    {

        // 数据库配置信息设置（全局有效）
        Console::init();

        foreach ($config['list_url'] as $url) {
            if (!in_array($url, self::$_listUrlList)) {
                self::$listUrlList[] = $url;
                self::$urlTimesObj[md5($url)] = self::$MAX_LIST_TIME;
            }
        }

        self::$MAX_LIST_TIME = isset($config['max_list_time']) ? $config['max_list_time'] : self::$MAX_LIST_TIME;
        self::$MAX_CONTENT_TIME = isset($config['max_content_time']) ? $config['max_content_time'] : self::$MAX_CONTENT_TIME;
        self::$MAX_REQUEST = isset($config['max_request']) ? $config['max_request'] : self::$MAX_REQUEST;
        self::$SLEEP_TIME = isset($config['sleep_time']) ? $config['sleep_time'] : self::$SLEEP_TIME;
    }

    public $after_field_func;

    /**
     * @param $config
     * @param  $loader ClassLoader
     * @throws \Exception
     */
    public function run($config, $loader)
    {
        self::init($config);
        $curl = new HttpCurl([], false);
        $content_num = 0;
        $itemObjArr = [];
        while (1) {
            if (count(self::$listUrlList) >= 1) {
                $listUrl = array_shift(self::$listUrlList);
                $list_key = md5($listUrl);
                Console::log('start spider list url ' . $listUrl);
                $curl->setReferrer($config['host']);
                $curl->get($listUrl);
                $listContent = $curl->response;

                //如果请求错误再爬一次 当累计错误超过最大值时 就停止爬当前URL
                if ($curl->error) {
                    self::$urlTimesObj[$list_key]--;
                    if (self::$urlTimesObj[$list_key] > 0) {
                        self::$listUrlList[] = $listUrl;
                    }
                    Console::error("fail to request $listUrl, " . $curl->error_message);
                    continue;
                }

                $page_list = new PageList($listContent, $config['preg_content'], $config['preg_list']);

                //从列表页发现列表页加入待爬列表
                $listArr = $page_list->getListUrlList();
                foreach ($listArr as $vo) {
                    $vo = strpos($vo, $config['host']) !== false ? $vo : $config['host'] . $vo;
                    $key_vo = md5($vo);
                    if (!isset(self::$urlTimesObj[$key_vo])) {
                        self::$listUrlList[] = $vo;
                        self::$urlTimesObj[$key_vo] = self::$MAX_LIST_TIME;
                    }
                }

                //如果列表页没有发现内容页再爬一次
                $contentArr = $page_list->getContentUrlList();
                if (empty($contentArr)) {
                    self::$urlTimesObj[$list_key]--;
                    if (self::$urlTimesObj[$list_key] > 0) {
                        self::$listUrlList[] = $listUrl;
                    }
                    Console::error('empty  content ' . $listUrl);
                    continue;
                }

                //从列表页发现内容页加入待爬列表
                foreach ($contentArr as $vo) {
                    $vo = strpos($vo, $config['host']) !== false ? $vo : $config['host'] . $vo;
                    $key_vo = md5($vo);
                    if (!isset(self::$urlTimesObj[$key_vo])) {
                        self::$contentUrlArr[] = $vo;
                        self::$urlTimesObj[$key_vo] = self::$MAX_CONTENT_TIME;
                    }
                }

            }
            if (count(self::$contentUrlArr) >= 1) {
                //拿到n个内容页并发执行
                for ($i = 0; $i < self::$MAX_REQUEST; $i++) {
                    $contentUrl = array_shift(self::$contentUrlArr);
                    if (!$contentUrl) break;
                    $key_vo = md5($contentUrl);
                    $itemObjArr[$key_vo] = new PageItem($contentUrl, $config['field_arr'], $loader,$config['db']);
                    $itemObjArr[$key_vo]->after_field_func = $this->after_field_func;
                    $itemObjArr[$key_vo]->start();
                    $content_num++;
                }
                Console::log("累计爬取总数:" . $content_num);
                //时间限制 可以根据网站qps设置
                usleep(self::$SLEEP_TIME * 1000);
            }

            if (count(self::$listUrlList) < 1 && count(self::$contentUrlArr) < 1) {
                break;
            }
        }
    }
}