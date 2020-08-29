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


$config2 = [
    'host' => 'http://www.gdyunan.gov.cn',
    'max_list_time' => 5,
    'max_content_time' => 3,
    'max_request' => 100,
    'sleep_time' => 1000,
    'list_url' => ['http://www.gdyunan.gov.cn/ynxrmzf/xwzx/zhxw/index.html'],
    'preg_content' => '/\/ynxrmzf\/xwzx\/\w+\/content\/post_\d+\.html/',
    'preg_list' => '/\/ynxrmzf\/xwzx\/\w+\/index_?\d*\.html/',
    'field_arr' => [
        ['name' => 'title', 'require' => true, 'selector' => '//div[@class="news-detail-info-title"]/text()'],
        ['name' => 'date', 'require' => true, 'selector_type' => 'regex', 'selector' => '/<span class="desc-span" style=\'color: #888;font-size: 14px;\'>(.*?)<\/span>/'],
        ['name' => 'content', 'require' => true, 'selector' => '//div[@class="news-detail-info-content"]'],
    ],
    'db' => $db_sqlite_config
];

$config3 = [
    'host' => 'https://www.88ys.com',
    'max_list_time' => 5,
    'max_content_time' => 3,
    'max_request' => 1,
    'sleep_time' => 1000,
    'list_url' => [
        'https://www.88ys.com/vod-type-id-1-pg-1.html',
        'https://www.88ys.com/vod-type-id-2-pg-1.html',
        'https://www.88ys.com/vod-type-id-3-pg-1.html',
        'https://www.88ys.com/vod-type-id-4-pg-1.html',
    ],
    'preg_content' => '/\/\w+\/\d+\/\d+\.html/',
    'preg_list' => '/\/vod-type-id-[1,4]-pg-\d+\.html/',
    'field_arr' => [
        ['name' => 'title', 'selector' => '//h1/text()'],
    ],
    'db' => $db_sqlite_config
];

$spider = new \Gsons\spider\Spider();
$spider->on_field = function ($data){
    foreach ($data as &$vo) {
        if (is_string($vo)) $vo = trim($vo);
    }
    \Gsons\Console::log($data);
    return $data;
};

$spider->run($config2, $__AUTOLOAD);
