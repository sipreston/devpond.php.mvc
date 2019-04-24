<?php

namespace Vision\VisionDatabase;

/**
 * Sets column change settings
 * Class DbColumnChange
 * @package Vision\VisionDatabase
 *
 * @property $column
 * @property $newDataType
 * @property $newLength
 * @property $newColumnName
 * @property $newForeignTable
 * @property $changeType
 * @property $columnExists
 * @property $keyChange
 */
class DbColumnChange
{
    /**
     * Column name
     * @var string
     * @property
     */
    public $column;

    /**
     * @var string
     * @property
     */
    public $newDataType;

    /**
     * Change the length the column is set to store
     * @var string
     * @property
     */
    public $newLength;

    /**
     * Change a column name
     * @var string
     */
    public $newColumnName;

    /**
     * Set a new table to point a foreign key to.
     * @var string
     */
    public $newForeignTable;

    /**
     * Set whether a column is to be added, removed or altered.
     * @var string
     */
    public $changeType;

    /**
     * Determine if the column is already existing
     * @var string
     */
    public $columnExists;

    /**
     * Set or remove a primary key
     * @var string
     */
    public $keyChange;

    const ALTER_TYPE = 'Alter';
    const ADD_TYPE = 'Add';
    const SET_PRIMARY = 'Set_Primary';
    const REMOVE_PRIMARY = 'Remove_Primary';
    const SET_FOREIGN = 'Set_Foreign';
    const REMOVE_FOREIGN = 'Remove_Foreign';

    public function __construct($column, $columnExists)
    {
        $this->columnExists = $columnExists;
    }

    /**
     * Determines whether the the column is to be added or altered.
     */
    public function setChangeType()
    {
        if($this->columnExists)
        {
            $this->changeType = self::ALTER_TYPE;
        }
        else
        {
            $this->changeType = self::ADD_TYPE;
        }
    }

    /**
     * @param bool $defKey
     * @param bool $colKey
     */
    public function setPrimaryKeyChange($defKey, $colKey)
    {
        if($defKey == true && $colKey == false) {
            $this->keyChange = self::SET_PRIMARY;
        }
        elseif($defKey == false && $colKey == true)
        {
            $this->keyChange = self::REMOVE_PRIMARY;
        }
        else
        {
            //2 keys are the same. How did you even get here?
        }
    }

    /**
     * @param bool $defKey
     * @param bool $colKey
     * @param string $mappedTable
     * @param string $foreignTable
     */
    public function setForeignKeyChange($defKey, $colKey, $mappedTable, $foreignTable)
    {
        if($defKey == true && $colKey == false)
        {
            $this->keyChange = self::SET_FOREIGN;
            $newForeign = array(
                'OrigTable' => $mappedTable,
                'OrigId' => '',
                'ForeignTable' => $foreignTable,
                'ForeignId' => ''
            );
        }

        elseif($defKey == false && $colKey == true)
        {
            $this->keyChange = self::REMOVE_FOREIGN;
        }
    }
}