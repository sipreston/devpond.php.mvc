<?php

namespace Vision\VisionDatabase;

use Vision\VisionModel\Model;

/**
 * Class DbSet
 * @package Vision\VisionDatabase
 *
 * @property $tableName
 * @property $className
 * @property $isRelatedTable
 * @property $relatedClass
 * @property $isValid
 */
class DbSet
{
    /**
     * Table name to store class data
     * @var string
     */
    private $tableName;

    /**
     * Class name mapped to the table
     * @var string
     */
    private $className;

    /**
     * Is the DbSet a relational mapping table
     * @var bool
     */
    private $isRelatedTable = false;

    /**
     * The mapped related class name.
     * @var string
     */
    private $relatedClass;

    /**
     * @var bool
     */
    private $isValid = false;

    /**
     * @var Model[]
     */
    private $objects;

    public function __construct($tableName, $className, $relatedClass = null)
    {
        if(!empty($tableName) && !empty($className))
        {
            $this->tableName = $tableName;
            $this->className = $className;
        }
        if($this->isRelatedTable   = (isset($relatedClass) ? true : false))
        {
            $this->relatedClass = $relatedClass;
        }
        $this->setIsValid();
    }

    /**
     * Get the table name related to this class
     * @return string
     */
    public function getTableName()
    {
        return (string)$this->tableName;
    }

    /**
     * Set the validity of the the DbSet
     */
    private function setIsValid()
    {
        if(!empty($this->tableName) && !empty($this->className))
        {
            if($this->isRelatedTable == false)
            {
                $this->isValid = true;
            }
            elseif($this->isRelatedTable == true && !empty($this->relatedClass))
            {
                $this->isValid = true;
            }
            else
            {
                $this->isValid = false;
            }
        }
        else
        {
            $this->isValid = false;
        }
    }

    /**
     * @param      $classA
     * @param null $classB
     * @return bool
     */
    public function isMappedTable($classA, $classB = null)
    {
        if(strtolower($classA) == strtolower($this->className) && (!isset($classB)))
        {
            return true;
        }
        elseif($this->isRelatedTable && strtolower($this->className) == strtolower($classA)
            && strtolower($this->relatedClass) == strtolower($classB))
        {
            return true;
        }
        elseif($this->isRelatedTable && strtolower($this->className) == strtolower($classB)
            && strtolower($this->relatedClass) == strtolower($classA))
        {
            return true;
        }
        return false;
    }

    /**
     * Add a model class to the set
     * @param Model $object
     */
    public function addObject(Model $object)
    {
        $this->objects[$object->Id] = $object;
    }

    /**
     * @param int $objectId
     * @return Model
     */
    public function getObject($objectId)
    {
        return $this->objects[$objectId];
    }

    /**
     * @param int $objectId
     * @return bool
     */
    public function objectExists($objectId)
    {
        if(count($this->objects)) {
            return array_key_exists($objectId, $this->objects);
        }
        return false;
    }
}