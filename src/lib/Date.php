<?php


namespace Gsons\lib;


class Date
{
    public static function time2second($time, $is_log = true)
    {
        if (is_numeric($time)) {
            $value = array(
                "years" => 0, "days" => 0, "hours" => 0,
                "minutes" => 0, "seconds" => 0,
            );
            if ($time >= 31556926) {
                $value["years"] = floor($time / 31556926);
                $time = ($time % 31556926);
            }
            if ($time >= 86400) {
                $value["days"] = floor($time / 86400);
                $time = ($time % 86400);
            }
            if ($time >= 3600) {
                $value["hours"] = floor($time / 3600);
                $time = ($time % 3600);
            }
            if ($time >= 60) {
                $value["minutes"] = floor($time / 60);
                $time = ($time % 60);
            }
            $value["seconds"] = floor($time);
            if ($is_log) {
                $t = $value["days"] . "d " . $value["hours"] . "h " . $value["minutes"] . "m " . $value["seconds"] . "s";
            } else {
                $t = $value["days"] . " days " . $value["hours"] . " hours " . $value["minutes"] . " minutes";
            }
            return $t;
        } else {
            return false;
        }
    }
}