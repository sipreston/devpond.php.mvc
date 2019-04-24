<?php

namespace Vision\VisionModel;

class ModelFactory
{
    public static function get($className, $id = null, $setCollections = true)
    {
        $modelMap = new ModelMap();
        $qualifiedName = 'Model\\' . $className;
        if(isset($id))
        {
            if (!$modelMap->isInMap($className, $id))
            {
                $id = $modelMap->addObject(new $qualifiedName($id));
            }
            $object = $modelMap->getObject($className, $id);
//            if($setCollections == true) {
//                $object->setCollections();
//            }
//            if($object->hasCollections() && $ob)
                $object->setCollections();
            return $modelMap->getObject($className, $id);
        }
        return new $qualifiedName;
    }
}