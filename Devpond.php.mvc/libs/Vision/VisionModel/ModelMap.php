<?php

namespace Vision\VisionModel;

use Vision\VisionFramework\Application;
use Vision\VisionFramework\Session;

class ModelMap
{
    const APPLICATION_NAME = 'Database';

    const APPLICATION_SUBNAME = 'DataModel';

    private $map = array();
    /**
     * @var Application
     */
    private $app;

    const MAP_NAME = 'ModelMap';

    public function __construct()
    {
        $this->app = new Application(self::APPLICATION_NAME);
//        If(Session::isInitialised()) {
//            //TODO: Initialise all the models here?
//            if (!array_key_exists(self::MAP_NAME, $_SESSION)) {
//                //TODO: This will go to a session handler later. Don't you worry.
//                $_SESSION[self::MAP_NAME] = array();
//            }
//        }


    }

    public function addObject(Model $object)
    {
        if(!$this->isModelObject($object))
            throw new \Exception('Passed object is of incorrect type');

            $className = $object->getClassName();
//            if(!array_key_exists($object->Id, $_SESSION[self::MAP_NAME][$className][$object->Id]))
//            {
        if($modelMap = $this->app->get('DataModel'))
        {
            $modelMap[$className]->addObject($object);
            $this->app->set('DataModel', $modelMap);
            return $object->Id;
        }
                //$_SESSION[self::MAP_NAME][$className][$object->Id] = $object;
        return false;
            //}
    }

    /**
     * @param $className
     * @param $objectId
     * @return Model | null
     */
    public function getObject($className, $objectId)
    {
        if($this->isInMap($className, $objectId))
        {
            $modelMap = $this->app->get('DataModel');
            $retObj = $modelMap[$className]->getObject($objectId);
            return $retObj;
        }
        return null;
    }

    public function isInMap($className, $objectId)
    {
        if($modelMap = $this->app->get('DataModel'))
        {
            if(array_key_exists($className, $modelMap))
            {
               return $modelMap[$className]->objectExists($objectId);
            }
        }
        return false;
    }

    private function IsModelObject($object)
    {
        return $object instanceof Model;
    }

    public function setMapOld($parent)
    {
        $app = $this->app;
        $results = array();
        foreach(get_declared_classes() as $class)
        {
            if(is_subclass_of($class, $parent))
            {
                $ref = new \ReflectionClass($class);
                $sn = $ref->getShortName();
                $results[$sn] = array();
            }
        }
        $this->modelMap = $results;
        $app->set('DataModel', $results);
       //$_SESSION[self::MAP_NAME] = $results;
    }

    public function setMap(array $dbSets)
    {
        $this->app->set('DataModel', $dbSets);
    }

    public function getMap()
    {
        if($this->isInitialised())
        {
            return $this->app->get('DataModel');
           // return $_SESSION[self::MAP_NAME];
        }
        return false;
    }

    public function isInitialised()
    {
        if($this->app->get('DataModel'))
        {
            return true;
        }
        return false;
    }

    public function isValidModelClass($className)
    {
        if($model = $this->app->get('DataModel'))
        {
            if(array_key_exists($className, $model))
            {
                return true;
            }
        }
//        if(array_key_exists($className, $_SESSION[self::MAP_NAME]))
//        {
//            return true;
//        }
        return false;
    }

    public static function clear()
    {
        $app = new Application('DataBase');
        $app->clear('DataModel');
        //unset($_SESSION[self::MAP_NAME]);
    }

}