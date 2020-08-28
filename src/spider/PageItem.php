<?php
/**
 * Created by PhpStorm.
 * User: gsonhub
 * Date: 2020-08-27
 * Time: 0:05
 */


namespace Gsons\spider;

use Gsons\HttpCurl;
use Composer\Autoload\ClassLoader;
use Gsons\Console;
use think\Db;

class PageItem extends \Worker
{
    private $fieldArr;

    private $contentUrl;

    public $on_field;

    public $after_content_func;

    const MAX_TIME = 3;

    /**
     * @var int 剩余请求次数
     */
    private $times;

    /**
     * @var ClassLoader
     */
    private $loader;

    private $dbConfig;

    /**
     * @var Store;
     */
    private $store;


    /**
     * PageItem constructor.
     * @param $contentUrl
     * @param $fieldArr
     * @param $loader ClassLoader
     * @param $dbConfig
     * @param $store Store
     */
    public function __construct($contentUrl, $fieldArr, $loader, $dbConfig, $store)
    {
        $this->fieldArr = $fieldArr;
        $this->contentUrl = $contentUrl;
        $this->times = self::MAX_TIME;
        $this->loader = $loader;
        $this->dbConfig = $dbConfig;
        $this->store = $store;
    }


    public function run()
    {
        $this->loader->register();
        $db_config = json_decode(json_encode($this->dbConfig), true);
        Db::setConfig($db_config);
        while ($this->times) {
            try {
                $curl = new HttpCurl([], false);
                $curl->setReferrer($this->contentUrl);
                $curl->get($this->contentUrl);
                if ($curl->error) {
                    throw  new \ErrorException($curl->error_message);
                }
            } catch (\Exception $e) {
                Console::error('fetch data failed ' . $this->contentUrl . ' ' . $e->getMessage());
                $this->times--;
                continue;
            }
            $this->times = 0;

            $content = $curl->response;

            if ($this->after_content_func) {
                $after_content = call_user_func($this->after_content_func, $content);
                if (is_string($after_content)) $content = $after_content;
            }
            $data = [];
            foreach ($this->fieldArr as $vo) {
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
            if ($this->on_field) {
                $data = call_user_func($this->on_field, $data);
                if (is_array($data)) {
                    $this->store->add();
                    //$this->store->push($data);
                    Db::table($db_config['table'])->insert($data);
                }
            }
        }
    }

}