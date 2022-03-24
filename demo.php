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

$config1 = [
    'host' => 'https://yp.120ask.com',
    'max_request' => 50,
    'list_url' => ['https://yp.120ask.com/search/0-0-1--0-0-0-0.html'],
    'preg_content' => '/\/detail\/(\d+)\.html/',
    'preg_list' => '/\/search\/0-0-\d+--0-0-0-0\.html/',
    'field_arr' => [
        ['name' => 'title', 'require' => true, 'selector' => '//div[@class="details-right-drug"]//p/text()'],
    ],
    'db' => $db_sqlite_config
];

$spider = new \Gsons\Spider($config1, $__AUTOLOAD);
$spider->on_field = function ($data,$contentUrl) {
    $data['content']=$contentUrl;
    \Gsons\lib\Console::log($data);
    return false;
};
$spider->start();


