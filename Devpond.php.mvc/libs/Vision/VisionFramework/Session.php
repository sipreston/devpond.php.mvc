<?php

namespace Vision\VisionFramework;

class Session
{
    public function __construct($startNow = false)
    {
        if($startNow === true)
        {
            self::start();
        }
    }

    public static function start()
    {
        session_start();
    }

    public static function end()
    {
        session_destroy();
    }

    public function __get($name)
    {
        return $_SESSION[$name];
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function merge($arrayName, $key, $arrayToMerge)
    {
        if(array_key_exists($_SESSION[$arrayName], $key))
        {
            array_merge($_SESSION[$arrayName], $arrayToMerge);
        }
    }

    public static function isInitialised()
    {
        if(isset($_SESSION))
        {
            return true;
        }
        return false;
    }
}