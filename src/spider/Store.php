<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020-08-28
 * Time: 17:16
 */

namespace Gsons\spider;


class Store extends \Threaded
{
    public $count = 0;

    public $list = [];

    public function add()
    {
        $this->count++;
    }
    public function push($item)
    {
        $this->list[] = $item;
    }
}