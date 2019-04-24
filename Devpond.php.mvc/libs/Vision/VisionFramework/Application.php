<?php

namespace Vision\VisionFramework;

class Application
{
    private $applicationName;

    const APPLICATION_STORE = 'Application';

    public function __construct($applicationName)
    {
        $this->applicationName = $applicationName;
        $this->setApplicationStore();
    }

    private function setApplicationStore()
    {
        if(!array_key_exists(self::APPLICATION_STORE, $GLOBALS))
        {
            $GLOBALS[self::APPLICATION_STORE] = array();
        }
        if(!array_key_exists($this->applicationName, $GLOBALS[self::APPLICATION_STORE]))
        {
            $GLOBALS[self::APPLICATION_STORE][$this->applicationName] = array();
        }
    }

    public function set($name, $value)
    {
        //TODO: This is a bit basic. We may want multidimensional searching later.
        if(array_key_exists($this->applicationName, $GLOBALS[self::APPLICATION_STORE]))
        {
            $GLOBALS[self::APPLICATION_STORE][$this->applicationName][$name] = $value;
        }
    }

    public function add($name, $value)
    {
        //TODO: This is a bit basic. We may want multidimensional searching later.
        if(array_key_exists($this->applicationName, $GLOBALS[self::APPLICATION_STORE]))
        {
            $GLOBALS[self::APPLICATION_STORE][$this->applicationName][$name][] = $value;
        }
    }

    public function get($name)
    {
        return $this->find($name);
    }

    private function find($name)
    {
        if(array_key_exists(self::APPLICATION_STORE, $GLOBALS)) {
            if (array_key_exists($this->applicationName, $GLOBALS[self::APPLICATION_STORE])) {
                if (array_key_exists($name, $GLOBALS[self::APPLICATION_STORE][$this->applicationName])) {
                    return $GLOBALS[self::APPLICATION_STORE][$this->applicationName][$name];
                }

            }
        }
        return null;
    }

    public function clear($name)
    {
        if(array_key_exists(self::APPLICATION_STORE, $GLOBALS)) {
            if (array_key_exists($this->applicationName, $GLOBALS[self::APPLICATION_STORE])) {
                if (array_key_exists($name, $GLOBALS[self::APPLICATION_STORE][$this->applicationName])) {
                    unset($GLOBALS[self::APPLICATION_STORE][$this->applicationName][$name]);
                }

            }
        }
    }
}