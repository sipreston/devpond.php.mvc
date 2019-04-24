<?php

namespace Vision\VisionDatabase;

/**
 * Class DbTableChange
 * @package Vision\VisionDatabase
 *
 * @property $tableName
 * @property $columnChanges
 * @property $changeType
 * @property $tableExists
 */
class DbTableChange
{
    /**
     * @var string
     */
    public $tableName;

    /**
     * @var DbColumnChange[]
     */
    public $columnChanges = array();

    /**
     * Set whether the table is to be created or altered.
     * @var string
     */
    public $changeType;

    /**
     * Set whether table exists or not.
     * @var bool
     */
    public $tableExists;

    const ALTER_TYPE = 'Alter';
    const CREATE_TYPE = 'Create';

    public function __construct($tableName, $tableExists)
    {
        $this->tableName = $tableName;
        $this->tableExists = $tableExists;
        $this->setChangeType();
    }

    /**
     * Set whether table is to be created or altered.
     */
    public function setChangeType()
    {
        if($this->tableExists)
        {
            $this->changeType = self::ALTER_TYPE;
        }
        else
        {
            $this->changeType = self::CREATE_TYPE;
        }
    }

    /**
     * @param DbColumnChange $change
     */
    public function addChange(DbColumnChange $change)
    {
        $this->columnChanges[$change->column] = $change;
    }

    /**
     * @return DbColumnChange[]
     */
    public function getChanges()
    {
        return $this->columnChanges;
    }

    /**
     * Check if any changes are to me made.
     * @return bool
     */
    public function hasChanges()
    {
        if(count($this->columnChanges) > 0)
        {
            return true;
        }
        return false;
    }
}
