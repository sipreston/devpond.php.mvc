<?php

namespace Vision\VisionModel;

use Vision\VisionDatabase\DbCollection;
use Vision\VisionDatabase\DbDataDefinition;
use Vision\VisionDatabase\DbHandler;
use Vision\VisionDatabase\DbRelatedTable;
use Vision\VisionDatabase\DbTableDefinition;

class Model
{
    /**
     * @var int
     */
    public $Id;

    protected $className;
    protected $tables = array();
    protected $dbName;
    /**
     * @var ModelMap
     */
    protected $modelMap;
    protected $pluraliseTables = true;
    protected $parentClass = 'Model';
    protected $databaseMap;
    /**
     * @var \ReflectionProperty[]
     */
    protected $collections;

    protected $hasCollections = false;

    protected $collectionsInitialised = false;

    private $relatedTablesToCreate = array();

    protected $db;

    const DATATYPE_STRING = 'string';
    const DATATYPE_INT = 'int';
    const DATATYPE_FLOAT = 'float';
    const DATATYPE_DECIMAL = 'decimal';
    const DATATYPE_BOOLEAN = 'bool';
    const DATATYPE_DATE = 'date';
    const DATATYPE_DATETIME = 'datetime';

    const DATE_FORMAT = 'Y-m-d h:i:s';

	public function __construct($id = null)
    {
        $this->modelMap = new ModelMap();
        $this->db = (new DbHandler())->getDbProvider();
        $this->dbName = $this->db->dbName;
        $this->className = $this->getClassName();
        //$this->className->onConstruct($id);
        //$this->addToMap();
    }
	public function save()
    {
        $class = $this->getClassName();
        $vars = $this->mapPropertiesToColumns($class, $this->getProperties(true));
        $tableName = $this->mapTableName($class);
        $result = $this->db->saveRow($tableName, $vars, $this->Id);

        if($result[0][0]['Id'])
        {
            //$data = $result[0][0];
            $this->Id = (int)$result[0][0]['Id'];
            //$this->populateValues($data);
        }
        $this->modelMap->addObject($this);
        $t=0;
    }
	public function delete()
    {

    }

    public function get($id)
    {
        $this->Id = (int)$id;
        $class = $this->getClassName();
        $vars = $this->mapPropertiesToColumns($class, $this->getProperties());
        if(count($this->collections) > 0)
        {
            $this->hasCollections = true;
        }
        if($result = $this->db->getRow($this->mapTableName($class), $vars, $this->Id))
        {
            $data = $result->get(1);
            $row = $data->getFirst();
            $this->populateValues($row, $vars);
        }
    }

    private function populateValues($props, $vars = array())
    {
        if(count($vars) == 0)
        {
            $vars = $this->mapPropertiesToColumns($this->getClassName(), $this->getProperties());
        }
        //$this->Id = (int)$props[$primaryKeyName];
        foreach($vars as $var)
        {
            $propName = $var->property;
            $mappedCol = $var->column;
            $value = $props->$mappedCol;
            $classType = $var->dataType;
            if($var->isIdentity == true)
            {
                $this->Id = (int)$value;
            }
            else if($var->isForeignKey)
            {
                $this->{$propName} = ModelFactory::get($var->dataType, $value);
            }
            else
            {
                $dataType = ltrim($var->dataType, '\\');
                switch(strtolower($dataType))
                {
                    case self::DATATYPE_INT:
                        $mappedVal = isset($value) ? (int)$value : null;
                        break;
                    case self::DATATYPE_STRING:
                        $mappedVal = isset($value) ? (string)$value : null;
                        break;
                    case self::DATATYPE_BOOLEAN:
                        $mappedVal = ($value == 1) ? true : false;
                        break;
                    case self::DATATYPE_FLOAT:
                    case self::DATATYPE_DECIMAL:
                        $mappedVal = isset($value) ? (float)$value : null;
                        break;
                    case self::DATATYPE_DATE:
                    case self::DATATYPE_DATETIME:
                        $mappedVal = isset($value) ? new \DateTime($value) : null;
                        break;
                    default:
                        $mappedVal = isset($value) ? $value : null;
                        break;

                }
                $this->{$propName} = $mappedVal;
            }
        }

        $t =0;
    }

    public function getClassName()
    {
        if(!isset($this->className)) {
            $refClass = new \ReflectionClass($this);
            $this->className = $refClass->getShortName();
        }
        return $this->className;
    }

    protected function getColumns($table)
    {
        return $this->db->getColumns($table);
    }

    private function saveTable($tableName, $tableDefinition)
    {

        //$tableName = $this->mapTableName($tableName);
        // create new table
        $this->db->saveTable($tableName, $tableDefinition);
    }

    public function getProperties($noId = false)
    {
        $refObj = new \ReflectionClass(get_class($this));
        $properties = $refObj->getProperties();
        $retProps = array();
        $testProps = array();
        if(!$this->modelMap->isInitialised())
        {
            $this->modelMap->setMap($this->parentClass);
            //$this->mapModel($this->parentClass);
        }
        foreach($properties as $prop)
        {
            $propName = $prop->getName();
            if($this->isCollection($prop))
            {
                $this->setCollection($prop);
            }
            else if (!$this->isModelProperty($prop))
            {
                    continue;
            }
            // For now model properties are determined by the upper case styling
            // this will change to look for any property with the @model annotation.
            else if (ctype_upper($propName[0]))
            {
                $testProp = new DbDataDefinition($prop, $this->className);
                $testProps[$testProp->property] = $testProp;
//                if (isset($testProp->property))
//                {
//                    $testProps[$testProp->property] = $testProp;
//                    $retProp = array(
//                        'Name' => null,
//                        'Type' => null,
//                        'Length' => null,
//                        'Nullable' => null
//                    );
//                    $propName = $prop->getName();
//                    if ($noId && $propName == 'Id') {
//                        continue;
//                    }
//                    if (ctype_upper($propName{0})) {
//                        $type = null;
//                        $length = null;
//                        $nullable = null;
//
//                        $doc = $prop->getDocComment();
//                        if (preg_match('/@var\s+([^\s]+)/', $doc, $varMatches)) {
//                            list(, $type) = $varMatches;
//                        }
//                        if (preg_match('/@length\s+([^\s]+)/', $doc, $lengthMatches)) {
//                            list(, $type) = $lengthMatches;
//                        }
//                        if (preg_match('/@nullable\s+([^\s]+)/', $doc, $nullMatches)) {
//                            list(, $type) = $nullMatches;
//                        }
//
//                        $retProp['Name'] = $propName;
//                        $retProp['Type'] = $type;
//                        $retProp['Length'] = $length;
//                        $retProp['Nullable'] = $nullable;
//                        $retProps[$propName] = $retProp;
//
//                        unset($varMatches, $lengthMatches, $nullMatches);
//                        unset($retProp);
//                    }
//                }
            }
        }
        return $testProps;

    }

    /**
     * @param string $className
     * @param DbDataDefinition $classProps
     * @param null $columns
     * @return DbDataDefinition[]
     */
    private function mapPropertiesToColumns($className, $classProps, $columns = null)
    {
        foreach($classProps as &$prop)
        {
            $propName = $prop->property;
            $propType = $prop->dataType;

            if($propName == 'Id')
            {
                if(!isset($this->$propName))
                {
                    unset($classProps[$propName]);
                    continue;
                }
                $prop->value = $this->$propName;
            }
            elseif($this->classIsMapped($propName))
            {
                $prop->value = $this->$propName->Id;
            }
            elseif($this->classIsMapped($propType))
            {
                $prop->value = $this->$propName->Id;
            }
            else
            {
                $prop->value = $this->$propName;
            }

            if(isset($columns))
            {
                if(in_array($prop->column, $columns))
                {
                    $prop->columnExists = true;
                    unset($columns[$prop->column]);
                }
            }
        }

        if(isset($columns))
        {
            foreach($columns as $col)
            {
                $mappedProp = $col;

                if(!in_array($col, $classProps))
                {
                    //work out if a column exists that isn't in a property
                }
            }
        }
        return $classProps;
    }

    private function mapTableName($class)
    {
        if($this->pluraliseTables == true)
        {
            return $class . 's';
        }
        return $class;
    }

    public function mapModel($parent)
    {
        $results = array();
        foreach(get_declared_classes() as $class)
        {
            if(is_subclass_of($class, $parent))
            {
                $results[$class] = array();
            }
        }
        $this->modelMap = $results;
    }

    protected function addToMap()
    {
        if(isset($this->Id)) {
            $this->modelMap->addObject($this);
//            $className = $this->getClassName();
//            $this->modelMap[$className][$this->Id] = $this;
            return true;
        }
        return false;
    }

    public function updateDbModel()
    {
        $this->tables = $this->db->getTables($this->dbName);
        //$this->mapModel($this->parentClass);
        $this->modelMap->setMap($this->parentClass);
        $fullModelMap = array(
            'ClassMap' => array(),
            'DatabaseMap' => array()
        );
        foreach($this->modelMap as $subClass)
        {
            $class = new $subClass;
            $className = get_class($class);
            $classProps = $class->getProperties();
            $fullModelMap['ClassMap'][$subClass] = $classProps;

            $mappedCols = $this->mapPropertiesToColumns($className, $classProps);
            $fullModelMap['DatabaseMap'][$this->mapTableName($subClass)] = $mappedCols;
        }

        $this->databaseMap = $fullModelMap['DatabaseMap'];
        $t=0;
        foreach($this->databaseMap as $tableName => $tableMap)
        {
            $this->saveTable($tableName, $tableMap);
        }
    }

    public function updateDbModelNew()
    {
        $this->tables = $this->db->getTables($this->dbName);
        //$this->mapModel($this->parentClass);
        $this->modelMap->setMap($this->parentClass);
        foreach($this->modelMap->getMap() as $modelClass => $value)
        {
            //$class = new $modelClass;
            $className = (new \ReflectionClass($modelClass))->getName();
           // $className = get_class($class);
            $modelTable = new DbTableDefinition($className, $this->modelMap);
            $fullModelMap['ClassMap'][$modelTable->tableName] = $modelTable;

            //$mappedCols = $this->mapPropertiesToColumns($className, $classProps);
            //$fullModelMap['DatabaseMap'][$this->mapTableName($subClass)] = $mappedCols;
        }

        $this->databaseMap = $fullModelMap['ClassMap'];
        foreach($this->databaseMap as $tableName => $table)
        {
            if($table instanceof DbTableDefinition)
            {
                if ($table->hasChanges() && $table->isValid())
                {
                    $table->save();
                }
                if($table->hasNewRelatedTables())
                {
                    $this->relatedTablesToCreate[$table->tableName] = $table->getRelatedTablesToCreate();
                }
            }
        }
        foreach($this->relatedTablesToCreate as $relatedTables)
        {
            foreach($relatedTables as $relatedTable)
            {
                if($relatedTable instanceof DbRelatedTable)
                {
                    $relatedTable->save();
                }
            }
        }
    }

    private function determineDatatype($prop)
    {

    }

    protected function isParent()
    {
        if(get_class($this) == $this->parentClass)
        {
            return true;
        }
        return false;
    }
    protected function isMapped($className, $id)
    {
        $searchKey = $this->modelMap[$className];
        if(array_key_exists($id, $searchKey))
            return true;

        foreach($searchKey as $object)
        {
            if($id == $object->Id)
                return true;
        }
        return false;
    }

    protected function classIsMapped($className)
    {
        if(!isset($this->modelMap))
            $this->modelMap = new ModelMap();

        if($this->modelMap->isValidModelClass($className))
            return true;

        return false;

//        if(isset($this->modelMap))
//        {
//            if(array_key_exists($className, $this->modelMap))
//                return true;
//        }
//        return false;
    }

    protected function isModelProperty(\ReflectionProperty $prop)
    {
        $doc = $prop->getDocComment();
        // Throw out collections
        if ((stripos(strtolower($doc), '[]') !== false)) {
           return false;
        }
        if (preg_match('/@model\s+([^\s]+)/', $doc, $varMatches)) {
            return true;
        }

        return true;
    }

    protected function isCollection(\ReflectionProperty $prop)
    {
        $doc = $prop->getDocComment();
        if ((stripos(strtolower($doc), '[]') !== false)) {
            return true;
        }

        return false;
    }

    protected function setCollection(\ReflectionProperty $prop)
    {
        $propName = $prop->getName();
        $type = $this->getCollectionType($prop);
        if($type) {
            $this->collections[$type] = $prop;
        }
//        $dbCol = new DbCollection($prop, $this->Id);
//        $this->{$propName} = $dbCol->getCollection();
    }

    public function setCollections()
    {
        if(!$this->collectionsAreInitialised() && $this->hasCollections()) {
            $this->collectionsInitialised = true;
            foreach ($this->collections as $collection) {
                $collectionName = $collection->getName();
                $dbCol = new DbCollection($collection, $this->Id);
                $this->{$collectionName} = $dbCol->getCollection();
            }

        }
    }

    public function collectionsAreInitialised()
    {
        // this is pretty basic at the moment. May want to check what collections have not been set, where poss.
        return $this->collectionsInitialised;
    }

    public function hasCollections()
    {
        return $this->hasCollections;
    }

    public function addToCollection($collectionObject)
    {
        $ref = new \ReflectionClass($collectionObject);
        if($this->isValidCollection($collectionObject))
        {

        }
    }

    /**
     * @param Model $collectionObject
     * @return bool
     */
    private function isValidCollection(Model $collectionObject)
    {
        return true;
        foreach($this->collections as $col)
        {
            $t = $col->class;
            $y = $collectionObject->getClassName();
            if($col->class == $collectionObject->getClassName())
            {
                return true;
            }
        }
        return false;
    }

    private function getCollectionType(\ReflectionProperty $prop)
    {
        $doc = $prop->getDocComment();
        if (preg_match('/@var\s+([^\s]+)/', $doc, $varMatches)) {
            list(, $type) = $varMatches;
        }
        if(!empty($type)) {
            $type = str_replace('[]', '', $type);
            return $type;
        }
        return false;
    }

    public static function getBaseModelClassdir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR;
    }
}