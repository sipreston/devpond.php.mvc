<?php

namespace Vision\VisionDatabase;

/**
 * Class DbColumnDefinition
 * @package Vision\VisionDatabase
 *
 * @property $columnName
 * @property $type
 * @property $mappedType
 * @property $length
 * @property $isNullable
 * @property $isIdentity
 * @property $isPrimaryKey
 * @property $isForeignKey
 * @property $autoIncrement
 * @property $isUnique
 */
class DbColumnDefinition
{
    /**
     * Column name
     * @var string
     */
    public $columnName;

    /**
     * Data type
     * @var string
     */
    public $type;

    /**
     * Data type mapped to the type in the DBMS
     * @var string
     */
    public $mappedType;

    /**
     * Amount of storage for the data type
     * @var string
     */
    public $length;

    /**
     * Data type accepts null values or not
     * @var bool
     */
    public $isNullable = true;

    /**
     * Column s a unique identifier
     * @var bool
     */
    public $isIdentity = false;

    /**
     * Column is the primary key for the table
     * @var bool
     */
    public $isPrimaryKey = false;

    /**
     * Column is a foreign key
     * @var bool
     */
    public $isForeignKey = false;

    /**
     * Set whether column increments in value on each new row
     * @var bool
     */
    public $autoIncrement = false;

    /**
     * @var bool
     */
    public $isUnique = false;

    /**
     * Map a DBMS data type to a PHP data type.
     * @param string $type
     * @return string
     */
    public function mapType($type)
    {
        $type = $this->getType($type);
        switch($type)
        {
            case 'int':
                return 'int';
            case 'varchar';
            case 'nvarchar':
            case 'text':
                return 'string';
            case 'datetime':
            case 'date':
                return 'DateTime';
            case 'bit':
                return 'bool';
            default:
                return null;
        }
    }

    /**
     * Get storage size of given column
     * @param string $type
     * @return null
     */
    public function getLength($type)
    {
        if(preg_match('#\((.*?)\)#', $type, $match)) {
            return $match[1]; // not sure I like that.
        }
        return null;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getType($type)
    {
        preg_match('#^[^\(]+#', $type, $match);
        return $match[0];
    }
}