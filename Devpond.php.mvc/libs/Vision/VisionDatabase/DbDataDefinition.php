<?php

namespace Vision\VisionDatabase;

use ReflectionProperty;
use Vision\VisionModel\ModelMap;

/**
 * Class DbDataDefinition
 * @package Vision\VisionDatabase
 *
 * @property  $property
 * @property  $column
 * @property $columnExists
 * @property $dataType
 * @property $length
 * @property $isPrimaryKey
 * @property $isForeignKey
 * @property $foreignTable
 * @property $isIdentity
 * @property $isNullable
 * @property $value
 * @property $pluraliseTables
 * @property $modelClass
 *
 */
class DbDataDefinition
{
    /**
     * @property
     * @var string
     */
    public $property;
    /**
     * @property
     * @var string
     */
    public $column;
    /**
     * @property
     * @var bool
     */
    public $columnExists;
    /**
     * @property
     * @var string
     */
    public $dataType;
    /**
     * @property
     * @var string
     */
    public $length = null;
    /**
     * @property
     * @var bool
     */
    public $isPrimaryKey;
    /**
     * @property
     * @var bool
     */
    public $isForeignKey = false;
    /**
     * @property
     * @var string
     */
    public $foreignTable;
    /**
     * @property
     * @var bool
     */
    public $isIdentity = false;
    /**
     * @property
     * @var bool
     */
    public $isNullable = true;
    /**
     * @property
     * @var mixed
     */
    public $value;
    /**
     * @property
     * @var bool
     */
    public $pluraliseTables = true;
    /**
     * @property
     * @var string
     */
    public $modelClass;
    /**
     * @var string
     */
    private $doc;
    /**
     * @property
     * @var ModelMap
     */
    private $modelMap;
    /**
     * @const string
     */
    const IDENTIFIER = 'Id';


    public function __construct(ReflectionProperty $prop, $modelClass)
    {
        $this->modelMap = new ModelMap();
        $this->modelClass = $modelClass;

        if($this->modelMap->isValidModelClass($modelClass) || self::IDENTIFIER == $prop->getName() ) {
            $this->property = $prop->getName();
            $this->doc = $prop->getDocComment();
            $this->isPrimaryKey = $this->isIdentity = $this->isIdentity();
            if (!empty($this->doc)) {
                $this->setType();
                $this->setLength();
                $this->isNullable();
            }
            if ($this->modelMap->isInitialised()) {

                $this->isForeignKey();
            }
            $this->setColumnName();
        }
    }

    /**
     * Check if the column is a primary key
     * @return bool
     */
    public function isIdentity()
    {
        if($this->property == self::IDENTIFIER)
        {
            return true;
        }
        return false;
    }

    /**
     * Check if column can contain null values
     * @return bool
     */
    public function isNullable()
    {
        if($this->property == self::IDENTIFIER)
        {
            $this->isNullable = false;
            return false;
        }
        if (preg_match('/@nullable\s+([^\s]+)/', $this->doc, $nullMatches)) {
            list(, $nullable) = $nullMatches;
            $this->isNullable = true;
            return true;
        }
        return false;
    }

    /**
     * Check if the column maps to another table
     */
    public function isForeignKey()
    {
//        if(isset($modelMap) && !isset($this->modelMap))
//        {
//            $this->modelMap = $modelMap;
//        }
        if($this->modelMap->isValidModelClass($this->dataType))
        //if(in_array($this->dataType, $this->modelMap))
        {
            $this->isForeignKey = true;
            $this->mapForeignTable();
        }
//        if(array_key_exists($this->dataType, $this->modelMap))
//        {
//            $this->isForeignKey = true;
//            $this->mapForeignTable();
//        }

    }

    /**
     * Map the foreign table name
     */
    public function mapForeignTable()
    {
        if($this->pluraliseTables == true)
        {
            $this->foreignTable = $this->dataType . 's';
        }
        else
        {
            $this->foreignTable = $this->dataType;
        }
    }

    public function columnExists()
    {

    }

    /**
     * Set datatype
     */
    public function setType()
    {
        if (preg_match('/@var\s+([^\s]+)/', $this->doc, $varMatches)) {
            list(, $type) = $varMatches;
            $this->dataType = $type;
        }
    }

    /**
     * Set length restriction
     */
    public function setLength()
    {
        if (preg_match('/@length\s+([^\s]+)/', $this->doc, $lengthMatches)) {
            list(, $length) = $lengthMatches;
            $this->length = $length;
        }
        elseif($this->dataType == 'int')
        {
            $this->length = '11';
        }
        elseif($this->dataType == 'float' || $this->dataType == 'decimal')
        {
            $this->length = '18,2';
        }
        elseif($this->dataType == 'string')
        {
            $this->length = '255';
        }
    }

    /**
     * Check if the class being checked is in the model
     * @param $modelMap
     * @return bool
     */
    private function isModelChild($modelMap)
    {
        if(array_key_exists($this->modelClass, $modelMap))
        {
            return true;
        }
        if(in_array($this->modelClass, $modelMap))
        {
            return true;
        }
        return false;
    }

    /**
     * Map the column name. Normal datatypes will use the property name given.
     * Foreign keys will have Id suffixed. Column name can be based on the property name or foreign class name.
     */
    public function setColumnName()
    {
        if($this->property == self::IDENTIFIER)
        {
            $this->column = $this->modelClass . self::IDENTIFIER;
        }
        elseif($this->isForeignKey)
        {
            if($this->property != $this->dataType) {
                //$this->column = $this->property . $this->dataType . self::IDENTIFIER;
                $this->column = $this->property;
            }
            else{
                $this->column = $this->property . self::IDENTIFIER;
            }
        }
        else
        {
            $this->column = $this->property;
        }
    }

    public function isValid()
    {
        return true;
    }

}


