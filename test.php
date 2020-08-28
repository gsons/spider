<?php

global $__AUTOLOAD;
$__AUTOLOAD = require_once "vendor/autoload.php";


$db_sqlite_config = [
    'type' => 'sqlite',
    'table'=>'article',
    'dsn' => 'sqlite:article.db',
    'charset' => 'utf8',
    'debug' => true,
];

$config1 = [
    'host' => 'http://www.gdyunan.gov.cn',
    'list_url' => ['http://www.gdyunan.gov.cn/ynxrmzf/xwzx/zhxw/index_13.html'],
    'preg_content' => '/\/ynxrmzf\/xwzx\/zhxw\/content\/post_\d+\.html/',
    'preg_list' => '/\/ynxrmzf\/xwzx\/zhxw\/index_\d+\.html/',
    'field_arr' => [
        ['name' => 'title', 'selector' => '//div[@class="news-detail-info-title"]/text()']
    ],
    'db' => $db_sqlite_config
];

$config2 = [
    'host' => 'http://www.gdyunan.gov.cn',
    'list_url' => ['http://www.gdyunan.gov.cn/ynxrmzf/xwzx/zhxw/index.html'],
    'preg_content' => '/\/ynxrmzf\/xwzx\/\w+\/content\/post_\d+\.html/',
    'preg_list' => '/\/ynxrmzf\/xwzx\/\w+\/index_?\d*\.html/',
    'field_arr' => [
        ['name' => 'title', 'selector' => '//div[@class="news-detail-info-title"]/text()']
    ],
    'db' => $db_sqlite_config
];

$config3 = [
    'host' => 'https://www.88ys.com',
    'list_url' => [
        'https://www.88ys.com/vod-type-id-1-pg-1.html',
        'https://www.88ys.com/vod-type-id-2-pg-1.html',
        'https://www.88ys.com/vod-type-id-3-pg-1.html',
        'https://www.88ys.com/vod-type-id-4-pg-1.html',
    ],
    'preg_content' => '/\/\w+\/\d+\/\d+\.html/',
    'preg_list' => '/\/vod-type-id-[1,4]-pg-\d+\.html/',
    'field_arr' => [
        ['name' => 'title', 'selector' => '//h1/text()']
    ],
    'db' => $db_sqlite_config
];

$spider = new \Gsons\spider\Spider();
$spider->after_field_func = function ($data) {
    foreach ($data as &$vo) {
        if (is_array($vo)) $vo = $vo[0];
        $vo = trim($vo);
    }
    \Gsons\Console::log($data);
    return $data;
};

try {
    $spider->run($config1, $__AUTOLOAD);
} catch (\Exception $e) {
}

