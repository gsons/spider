<?php
/**
 * Created by PhpStorm.
 * User: gsonhub
 * Date: 2019/7/7
 * Time: 3:29
 */

namespace Gsons\lib;


class Console
{
    const FILE_ERROR = "./cache/error.log";

    const FILE_DEBUG = "./cache/log.log";

    static $pid;

    public static function init()
    {
        if (!self::$pid) {
            if (!is_dir('cache')) {
                @mkdir('cache');
            }
            self::$pid = getmypid();
        }
    }

    public static function log($msg, $isGBK = false)
    {
        self::init();
        if (is_array($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $date = 'Process Id:' . self::$pid . ' ' . date('Y-m-d H:i:s');
        $msg = $date . '【INFO】 ' . $msg . PHP_EOL;
        file_put_contents(self::FILE_DEBUG, $msg, FILE_APPEND | LOCK_EX);
        if ($isGBK) {
            $msg = iconv('UTF-8', 'gbk//IGNORE', $msg);
        }
        //echo $msg;
    }

    public static function logEOL()
    {
        self::init();
        //echo PHP_EOL;
        file_put_contents(self::FILE_DEBUG, PHP_EOL, FILE_APPEND | LOCK_EX);
    }


    /**
     * @param $param mixed
     */
    public static function error($param)
    {
        self::init();
        if ($param instanceof \Exception) {
            $traceline = "#%s %s(%s): %s(%s)";
            $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

            $trace = $param->getTrace();
            // print_r($trace);
            foreach ($trace as $key => $stackPoint) {
                $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
            }

            $result = array();
            foreach ($trace as $key => $stackPoint) {
                $result[] = sprintf(
                    $traceline,
                    $key,
                    isset($stackPoint['file']) ? $stackPoint['file'] : '',
                    isset($stackPoint['line']) ? $stackPoint['line'] : '',
                    isset($stackPoint['function']) ? $stackPoint['function'] : '',
                    implode(', ', isset($stackPoint['args']) ? $stackPoint['args'] : '')
                );
            }
            $result[] = '#' . ++$key . ' {main}';
            $msg = sprintf(
                $msg,
                get_class($param),
                $param->getMessage(),
                $param->getFile(),
                $param->getLine(),
                implode("\n", $result),
                $param->getFile(),
                $param->getLine()
            );
        } else {
            $msg = is_array($param) ? json_encode($param, true, JSON_UNESCAPED_UNICODE) : $param;
        }
        $date = 'Process Id ' . self::$pid . ' ' . date("Y-m-d H:i:s") . "【error】" . PHP_EOL;
        $msg = $date . $msg . PHP_EOL;
        file_put_contents(self::FILE_ERROR, $msg, FILE_APPEND | LOCK_EX);
        //echo $msg;
    }
}