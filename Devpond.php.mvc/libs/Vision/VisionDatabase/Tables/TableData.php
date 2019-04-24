<?php

namespace Vision\VisionDatabase\Tables;

use Vision\VisionDatabase\DbColumnDefinition;

/**
 * Class TableData
 * @package Vision\VisionDatabase\Tables
 */
class TableData
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var DbColumnDefinition
     */
    private $columnDefinitions = [];

    /**
     * TableData constructor.
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = (string)$tableName;
    }

    /**
     * @param DbColumnDefinition $definition
     */
    public function addColumnDefinition(DbColumnDefinition $definition)
    {
        $this->columnDefinitions[] = $definition;
    }

    /**
     * @return DbColumnDefinition
     */
    public function getColumnDefinitions()
    {
        return $this->columnDefinitions;
    }
}
