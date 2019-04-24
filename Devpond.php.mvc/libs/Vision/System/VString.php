<?php

namespace Vision\System;
/**
 * This library allows strings to perform checks upon itself, return formatted versions of itself
 * Replace instances of patterns, in itself, to something else.
 *
 * When the object is called by string functions (i.e. sprintf, (string), echo),
 * it will return the string version of itself.
 *
 * Class VString
 * @package System
 */
class VString
{
    /**
     * @var string
     */
    private $originalString;
    /**
     * @var string
     */
    private $formattedString;
    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * String constructor.
     * @param string $string
     * @param string $encoding
     */
    public function __construct($string = null, $encoding = 'UTF-8')
    {
        if(isset($string))
        {
            $this->set($string);
        }
        $this->encoding = $encoding;
    }

    /**
     * @param string $string
     */
    public function set($string)
    {
        $this->originalString = $string;
        $this->formattedString = $string;
    }

    /**
     * Removes white spacing
     * @return String
     */
    public function trim()
    {
        $this->formattedString = trim($this->formattedString);
        return $this;
    }

    /**
     * Find an occurrence in a string
     * @param $needle
     * @param bool $caseSensitive
     * @return bool
     */
    public function contains($needle, $caseSensitive = true)
    {
        $hayStack = $this->formattedString;
        if(!$caseSensitive)
        {
            $needle = strtolower($needle);
            $hayStack = strtolower($hayStack);
        }
        if(strpos($hayStack, $needle) !== false)
        {
            return true;
        }
        return false;
    }

    /**
     * Similar functionality to sprintf but returns the object instance, rather than just a string
     * @param string $string
     * @return String
     */
    public function format($string)
    {
        $args = func_num_args();
        $string = func_get_arg(0);
        for($i = 1; $i < $args; $i++)
        {
            $arg = func_get_arg($i);
            $string = preg_replace('/%s/', $arg, $string, 1);
        }
        $this->originalString = $string;
        $this->formattedString = $string;

        return $this;
    }

    /**
     * Create a new string with this method, without initialising the object directly.
     * @param string $string
     * @return String
     */
    public static function formatS($string)
    {
        $args = func_num_args();
        $string = func_get_arg(0);
        for($i = 1; $i < $args; $i++)
        {
            $arg = func_get_arg($i);
            $string = preg_replace('/%s/', $arg, $string, 1);
        }
        return new VString($string);
    }

    public function length()
    {
        return strlen($this->formattedString);
    }
    /**
     * Replace the first instance, or all instances.
     * @param string $match
     * @param string $replacement
     * @param string $delimiter
     * @param bool $allOccurrences
     * @return String
     */
    public function replace($match, $replacement, $delimiter = '/', $allOccurrences = true)
    {
        $string = $this->formattedString;
        $pattern = (string)$delimiter . (string)$match . (string)$delimiter;
        if($allOccurrences)
        {
            $string = preg_replace((string)$pattern, (string)$replacement, (string)$string);
        }
        else
        {
            $string = preg_replace((string)$pattern, (string)$replacement, (string)$string, 1);
        }
        $this->formattedString = $string;
        return $this;
    }

    /**
     * Remove characters from the start of the string
     * @param int $num
     * @return String
     */
    public function removeFirst($num = 1)
    {
        $this->formattedString = mb_substr($this->formattedString, $num, mb_strlen($this->formattedString), $this->encoding);
        return $this;
    }

    /**
     * Remove characters from the end of the string
     * @param int $num
     * @return String
     */
    public function removeLast($num = 1)
    {
        $this->formattedString = mb_substr($this->formattedString, 0, -$num, $this->encoding);
        return $this;
    }

    /**
     * @param string $needle
     * @param bool $caseSensitive
     * @return bool
     */
    public function startsWith($needle, $caseSensitive = true)
    {
        $hayStack = $this->formattedString;
        $len = strlen($needle);
        if(!$caseSensitive)
        {
            $hayStack = strtolower($hayStack);
            $needle = strtolower($needle);
        }
        return (substr($hayStack, 0, $len) === $needle);
    }

    /**
     * @param string $needle
     * @param bool $caseSensitive
     * @return bool
     */
    public function endsWith($needle, $caseSensitive = true)
    {
        $hayStack = $this->formattedString;
        $len = strlen($needle);
        if(!$caseSensitive)
        {
            $hayStack = strtolower($hayStack);
            $needle = strtolower($needle);
        }
        return $len === 0 || (substr($hayStack, -$len) === $needle);
    }

    /**
     * Revert the string to it's original status
     * @return String
     */
    public function reset()
    {
        $this->formattedString = (string)$this->originalString;
        return $this;
    }

    public function toLower()
    {
        $this->formattedString = strtolower($this->formattedString);
        return $this;
    }

    public function toUpper()
    {
        $this->formattedString = strtoupper($this->formattedString);
        return $this;
    }

    /**
     * @return null|string
     */
    public function toString()
    {
        return $this->formattedString;

    }

    /**
     * Returns the formatted string regardless
     * @param $name
     * @return null|string
     */
    public function __get($name)
    {
        return $this->formattedString;
    }

    /**
     * Any function that requests to print the object (i.e. echo, sprintf)
     * will get the final string
     * @return null|string
     */
    public function __toString()
    {
        return $this->formattedString;
    }
}