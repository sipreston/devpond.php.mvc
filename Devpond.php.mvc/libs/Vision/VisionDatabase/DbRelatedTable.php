<?php

namespace Vision\VisionDatabase;

use Vision\VisionDatabase\Interfaces\IDatabase;

/**
 * Class DbRelatedTable
 * @package Vision\VisionDatabase
 *
 * @property $db
 * @property $parts
 * @property $newTableName
 * @property $existingTables
 */
class DbRelatedTable
{
    const TABLE_PLURAL = 's';

    /**
     * @var IDatabase
     */
    private $db;

    /**
     * @var array
     */
    private $parts = array();

    /**
     * @var string
     */
    private $newTableName = null;

    /**
     * @var bool
     */
    private $createTable = false;

    /**
     * @var array|mixed
     */
    private $existingTables = array();

    public function __construct($tableName, $relatedTable, $modelClass)
    {
        $this->db = (new DbHandler())->getDbProvider();
//        if($this->isPlural($relatedTable))
//        {
//            $relatedTable = substr($relatedTable, 0, -1);
//        }
        $this->setParts($relatedTable, $modelClass);
        $this->existingTables = $this->db->getTables();
        if(!$this->tableExists())
        {
            $this->createTable = true;
            $this->newTableName = $this->parts[0] . $this->parts[1] . self::TABLE_PLURAL;
        }
    }

    /**
     * @return bool
     */
    public function tableExists()
    {
        $firstMatch = strtolower($this->parts[0]) . strtolower($this->parts[1]) . strtolower(self::TABLE_PLURAL);
        $secondMatch = strtolower($this->parts[1]) . strtolower($this->parts[0]) . strtolower(self::TABLE_PLURAL);

        foreach($this->existingTables as $table)
        {
            if($firstMatch == strtolower($table) || $secondMatch == strtolower($table))
                return true;
        }

        return false;
    }

    /**
     * @param $tableName
     * @return bool
     * @Deprecated
     */
    private function depluralise($className)
    {
        if(substr($className, -1, 1) == self::TABLE_PLURAL) {
            $className = substr($className, 0, -1);
        }

        return $className;
    }

    /**
     * @param string $partA
     * @param string $partB
     */
    private function setParts($partA, $partB)
    {
        $this->parts[] = $this->depluralise($partA);
        $this->parts[] = $this->depluralise($partB);
    }

    /**
     * @param $tableName
     * @return array[]|false|string[]
     */
    private function getClasses($tableName)
    {
        return preg_split('/(?=[A-Z])/', $tableName, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return bool
     */
    public function shouldCreateTable()
    {
        return $this->createTable;
    }

    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->newTableName;
    }

    /**
     * @return mixed
     */
    public function getForeignKeys()
    {
        return $this->parts;
    }

    /**
     * @return mixed
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     *
     */
    public function save()
    {
        if ($this->shouldCreateTable())
        {
            $this->db->saveRelatedTable($this);
        }
    }
}