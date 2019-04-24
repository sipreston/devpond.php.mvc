<?php

namespace Vision\VisionDatabase;

use ReflectionClass, ReflectionException;
use Vision\VisionDatabase\Interfaces\IDatabase;
use Vision\VisionModel\ModelMap;

/**
 * Class DbTableDefinition
 * @package Vision\VisionDatabase
 *
 * @property $tableName
 * @property $dataDefinitions
 * @property $pluraliseTables
 */
class DbTableDefinition
{
    /**
     * @var string
     */
    public $tableName;

    /**
     * @var DbDataDefinition[]
     */
    public $dataDefinitions = array();

    /**
     * @var bool
     */
    public $pluraliseTables = false;

    private $colCount;

    /**
     * List of columns currently in a table
     * @var array
     */
    private $existingCols = array();

    /**
     * @var IDatabase
     */
    private $db;

    /**
     * Class name the table maps to
     * @var string
     */
    private $modelClass;

    /**
     * @var ModelMap
     */
    private $modelMap;

    /**
     * Are there any changes to do.
     * @var bool
     */
    private $hasChanges = false;

    /**
     * @var DbColumnChange[]
     */
    private $changes = array();

    /**
     * Set whether table exists or not
     * @var bool
     */
    private $tableExists;

    /**
     * @var string[]
     */
    private $relatedTables = array();

    /**
     * @var DbRelatedTable[]
     */
    private $newRelatedTables = array();

    const PARENT_CLASS = 'Model';
    const IDENTIFIER = 'Id';
    const LAST_CHAR_INDEX = -1;
    const COLLECTION_TYPE = 'array';

    public function __construct($tableName, $modelClass)
    {
        $this->modelMap = new ModelMap();
        $this->modelClass = $modelClass;
        $this->db = (new DbHandler())->getDbProvider();
        $this->setTableName();
        if($this->modelMap->isInitialised()) {
            $this->getDataDefinitions();
        }
        if(isset($this->tableName)) {
            $this->getColumns();
        }
        $this->checkDataToColumnMatching();
        if(count($this->relatedTables) > 0)
        {
            foreach($this->relatedTables as $relatedTable) {
                $relTable = new DbRelatedTable('', $relatedTable, $modelClass);
                if ($relTable->shouldCreateTable()) {
                    $this->newRelatedTables[] = $relTable;
                }
            }
        }
    }

    /**
     * @param null $modelClass
     * @throws ReflectionException
     */
    public function getDataDefinitions($modelClass = null)
    {
        if(!isset($modelClass))
        {
            $modelClass = $this->modelClass;
        }
        $refObj = new ReflectionClass($modelClass);
        $objProps = $refObj->getProperties();
        $dataDefs = array();
        $relData = array();
        foreach($objProps as $objProp)
        {
            $propName = $objProp->getName();
            $propClass = $objProp->getDeclaringClass()->getName();
            $propAn = $objProp->getDocComment();
            $propType = $this->getType($propAn);
            if($this->isClassProperty($propName, $propClass, $propType)) {
                $dataDef = new DbDataDefinition($objProp, $modelClass);
                if ($dataDef->isValid()) {
                    $dataDefs[$dataDef->property] = $dataDef;
                }
            }
            elseif($this->isRelation($propName, $propAn))
            {
                $relData[] = $propName;
            }
        }
        $this->dataDefinitions = $dataDefs;
        $this->relatedTables = $relData;
    }

    /**
     * Check it table has a unique identifier
     * @return bool
     */
    public function hasIdentity()
    {
        foreach($this->dataDefinitions as $dataDef)
        {
            if($dataDef->isIdentity)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if there are any changes to make
     * @return bool
     */
    public function hasChanges()
    {
        if(count($this->changes) > 0)
        {
            return true;
        }
        if($this->hasChanges == true)
        {
            return true;
        }
        return false;
    }

    public function setRelationshipType()
    {

    }

    /**
     * @deprecated : The DbSet should have this information
     */
    public function setTableName()
    {
        $tableName = $this->modelClass;
        if($this->pluraliseTables == true && substr($tableName, self::LAST_CHAR_INDEX) != 's')
        {
            $tableName .= 's';
        }
       $this->tableName = $tableName;
    }

    /**
     * Save the object data as a table in the DBMS
     */
    public function save()
    {
        if($this->hasChanges())
        {
            $this->db->saveTable($this->tableName, $this);
        }
        if($this->hasNewRelatedTables())
        {
            foreach($this->newRelatedTables as $relTable)
            {
                $this->db->saveRelatedTable($relTable);
            }
        }
    }

    public function mapDefinitionsToColumns()
    {

    }

    /**
     * Get the existing columns in a table.
     */
    private function getColumns()
    {
        $this->existingCols = $this->db->getColumns($this->tableName);
        if(count($this->existingCols) > 0)
        {
            $this->tableExists = true;
        }
    }

    public function isValid()
    {
        return true;
    }

    /**
     * @param string $propName
     * @param string $propClass
     * @param string $propType
     * @return bool
     */
    private function isClassProperty($propName, $propClass, $propType)
    {
        //if($propType == self::COLLECTION_TYPE && ctype_upper($propName{0}))
        if($propType == self::COLLECTION_TYPE)
        {
//            $classType = substr($propName, 0, -1);
//            if(array_key_exists($classType, $this->modelMap))
//            {
//                return true;
//            }
            // arrays inside model objects are determined to be inumerables.
            return false;
        }
        if($propClass == self::PARENT_CLASS && $propName == self::IDENTIFIER)
        {
            return true;
        }
        if(ctype_upper($propName{0}) && $propClass != self::PARENT_CLASS)
        {
            return true;
        }
        return false;
    }

    /**
     *
     */
    public function checkDataToColumnMatching()
    {
        $tableChange = new DbTableChange($this->tableName, $this->tableExists);
        foreach($this->dataDefinitions as $def)
        {
            $changes = array();
            $colName = $def->column;
            $col = $this->existingCols[$colName];
            if($def->column != $col->columnName)
            {
                $change = new DbColumnChange($col, $def->columnExists);
                $change->newColumnName = $def->column;

                $tableChange->addChange($change);
                unset($change);
            }
            if($def->dataType != $col->mappedType)
            {
                $change = new DbColumnChange($col, $def->columnExists);
                $change->newDataType = $def->dataType;
                $change->newLength = $def->length;

                $tableChange->addChange($change);
                unset($change);
            }
            if(isset($def->length) && $def->length != $col->length)
            {
                $change = new DbColumnChange($col, $def->columnExists);
                $change->newLength = $def->length;

                $tableChange->addChange($change);
                unset($change);
            }
            if($def->isPrimaryKey != $col->isPrimaryKey)
            {
                $change = new DbColumnChange($col, $def->columnExists);
                $change->setPrimaryKeyChange($def->isPrimaryKey, $col->isPrimaryKey);
                $tableChange->addChange($change);
                unset($change);
            }

        }
        $t=0;
    }

    /**
     * @param string $propName
     * @param string $propAn
     * @return bool
     */
    private function isRelation($propName, $propAn)
    {
        if (preg_match('/@var\s+([^\s]+)/', $propAn, $varMatches)) {
            list(, $arrType) = $varMatches;
            $arrType = str_replace("[]", "", $arrType);
            if($this->modelMap->isValidModelClass($arrType))
            //if (array_key_exists($arrType, $this->modelMap))
            {
                //$this->relatedTables[] = $arrType;
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $propAn
     * @return null|string
     */
    private function getType($propAn)
    {
        if (preg_match('/@var\s+([^\s]+)/', $propAn, $varMatches)) {
            list(, $type) = $varMatches;
            if(strpos($type, '[]') == true)
                return self::COLLECTION_TYPE;
            return $type;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasNewRelatedTables()
    {
        if(count($this->relatedTables) > 0)
            return true;

        return false;
    }

    /**
     * @return DbRelatedTable[]
     */
    public function getRelatedTablesToCreate()
    {
        return $this->newRelatedTables;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
}
