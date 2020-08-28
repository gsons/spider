<?php
/**
 * Created by PhpStorm.
 * User: gsonhub
 * Date: 2020-08-27
 * Time: 0:05
 */

namespace Gsons\spider;


class PageList
{
    private $contentUrlList = [];
    private $listUrlList = [];

    public function __construct($content, $preg_content_url, $preg_list_url)
    {
        $contentUrlArr = Selector::select($content, $preg_content_url, 'regex');

        if ($contentUrlArr) $this->contentUrlList = is_string($contentUrlArr) ? [$contentUrlArr] : $contentUrlArr;

        $listUrlArr = Selector::select($content, $preg_list_url, 'regex');

        if ($listUrlArr) $this->listUrlList = is_string($listUrlArr) ? [$listUrlArr] : $listUrlArr;
    }

    /**
     * @return array|mixed
     */
    public function getContentUrlList()
    {
        return $this->contentUrlList;
    }

    /**
     * @return array|mixed
     */
    public function getListUrlList()
    {
        return $this->listUrlList;
    }
}