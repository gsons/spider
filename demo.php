<?php

global $__AUTOLOAD;
$__AUTOLOAD = require_once "vendor/autoload.php";

$db_sqlite_config = [
    'type' => 'sqlite',
    'table' => 'article',
    'dsn' => 'sqlite:article.db',
    'charset' => 'utf8',
    'debug' => false,
];

$db_mysql_config = [
// 数据库类型
    'type' => 'mysql',
    // 服务器地址
    'hostname' => '127.0.0.1',
    // 数据库名
    'database' => 'fast',
    // 数据库用户名
    'username' => 'root',
    // 数据库密码
    'password' => 'root',
    // 数据库连接端口
    'hostport' => '3306',
    // 数据库连接参数
    'params' => [],
    // 数据库编码默认采用utf8
    'charset' => 'utf8',
    // 数据库表前缀
    'prefix' => 'fa_',
    'table' => 'fa_video',
];

$config1 = [
    'host' => 'https://yp.120ask.com',
    'max_request' => 25,
    'list_url' => ['https://yp.120ask.com/search/0-0-1--0-0-0-0.html'],
    'preg_content' => '/\/detail\/(\d+)\.html/',
    'preg_list' => '/\/search\/0-0-\d+--0-0-0-0\.html/',
    'field_arr' => [
        ['name' => 'title', 'require' => true, 'selector' => '//div[@class="details-right-drug"]//p/text()'],
    ],
    'db' => $db_sqlite_config
];

$config2 = [
    'host' => 'https://detail.zol.com.cn',
    'max_request' => 100,
    'list_url' => ['https://detail.zol.com.cn/cell_phone_index/subcate57_0_list_1_0_9_2_0_3.html'],
    'preg_content' => '/\/cell_phone\/index\d+\.shtml/',
    'preg_list' => '/\/cell_phone_index\/subcate57_0_list_1_0_9_2_0_\d+\.html/',
    'field_arr' => [
        ['name' => 'title', 'require' => true, 'selector' => '//h1[@class="product-model__name"]/text()'],
    ],
    'db' => $db_sqlite_config
];


$config3 = [
    'host' => 'https://www.88hd.tv',
    'max_request' => 1,
    'list_url' => ['https://www.88hd.tv/vod-type-id-1-pg-2.html'],
    'preg_content' => '/\/\w+\/\d+\/\d+\.html/',
    'preg_list' => '/\/vod-type-id-1-pg-\d+\.html/',
    'field_arr' => [
        [
            'name' => 'title',
            'require' => true,
            'selector' => '//h1/text()'
        ],
        [
            'name' => 'image',
            'require' => false,
            'selector' => '//div[@class="ct-l"]//img/@src'
        ],
        [
            'name' => "time_text",
            'selector' => '//span[contains(./text(), "更新：")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "star",
            'selector' => '//span[contains(./text(), "主演：")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "leader",
            'selector' => '//span[contains(./text(), "导演：")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "year",
            'selector' => '//span[contains(./text(), "年份：")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "area",
            'selector' => '//span[contains(./text(), "地区：")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "desc",
            'selector' => '//span[contains(./text(), "剧情简介:")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "type",
            'selector' => '//span[contains(./text(), "类型：")]/following::text()[1]',
            'required' => false
        ],
        [
            'name' => "lang",
            'selector' => '//span[contains(./text(), "语言：")]/following::text()[1]',
            'required' => false
        ]
    ],
    'db' => $db_mysql_config
];

$config4 = [
    'host' => 'http://www.gdyunan.gov.cn',
    'max_request' => 60,
    'list_url' => ['http://www.gdyunan.gov.cn/ynxrmzf/xwzx/zhxw/index.html'],
    'preg_content' => '/\/ynxrmzf\/xwzx\/zhxw\/content\/post_\d+\.html/',
    'preg_list' => '/\/ynxrmzf\/xwzx\/zhxw\/index_\d+\.html/',
    'field_arr' => [
        ['name' => 'title', 'require' => true, 'selector' => '//h1/text()'],
    ],
    'db' => $db_sqlite_config
];

$spider = new \Gsons\Spider($config3, $__AUTOLOAD);
$spider->on_field = function ($data, $contentUrl,$content) {
    \Gsons\lib\Console::log($data);
    $data['cid']=$contentUrl;
    $data['time']=strtotime($data['time_text']);
//    $urlArr=\Gsons\lib\Selector::select($content,'//div[@id="vlink_1"]//ul//li//a/@href');
//    $host = $this->config['host'];
//    if(!empty($urlArr)&&is_array($urlArr)){
//        foreach ($urlArr as $url){
//            $url = strpos($url, $host) !== false ? $url : $host . $url;
//        }
//    }
    return $data;
};
$spider->exec();



