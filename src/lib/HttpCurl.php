<?php
/**
 * Created by PhpStorm.
 * User: gsonhub
 * Date: 2020-08-27
 * Time: 0:05
 */

namespace Gsons\lib;


use Curl\Curl;

class HttpCurl extends Curl
{
    //默认的useragent
    private $userAgentArr = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/84.0.4147.135 Safari/537.36 Edg/84.0.522.63'
    ];
    //默认的IP
    private $ipArr = [
        '112.94.101.142',
        '112.94.102.142',
        '112.94.103.142',
        '112.94.104.142',
        '112.94.105.142'
    ];

    const TIME_OUT = 5;

    public function __construct($config = [], $init = true)
    {
        if ($init) {
            parent::__construct();
        } else {
            $this->curl = curl_init();
            $this->setOpt(CURLINFO_HEADER_OUT, true);
            $this->setOpt(CURLOPT_HEADER, false);
            $this->setOpt(CURLOPT_RETURNTRANSFER, true);
            $this->setOpt(CURLOPT_HEADERFUNCTION, array($this, 'addResponseHeaderLine'));
        }
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $this->setOpt(CURLOPT_CONNECTTIMEOUT, self::TIME_OUT);
        $this->setOpt(CURLOPT_TIMEOUT, self::TIME_OUT);

        if (isset($config['user_agent']) && is_array($config['user_agent'])) {
            $this->userAgentArr = array_merge($this->userAgentArr, $config['user_agent']);
        }
        if (isset($config['ip']) && is_array($config['ip'])) {
            $this->ipArr = array_merge($this->ipArr, $config['ip']);
        }
        if ($init) {
            $this->initOpt();
        }
    }

    private function initOpt()
    {
        $index = array_rand($this->userAgentArr);
        if (isset($this->userAgentArr[$index])) {
            $this->setUserAgent($this->userAgentArr[$index]);
        }

        $index = array_rand($this->ipArr);
        if (isset($this->ipArr[$index])) {
            $this->setOpt(CURLOPT_HTTPHEADER, $this->ipArr[$index]);
        }

    }

    public function __destruct()
    {
        parent::__destruct();
    }
}