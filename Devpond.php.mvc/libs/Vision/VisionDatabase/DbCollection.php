<?php

namespace Vision\VisionDatabase;

use ReflectionProperty;
use Vision\VisionDatabase\Interfaces\IDatabase;
use Vision\VisionModel\ModelFactory;

/**
 * Class DbCollection
 * @package Vision\VisionDatabase
 *
 * @property $parentClass
 * @property $baseClass
 * @property $baseClassId
 * @property $collectionClass
 * @property $doc
 */
class DbCollection
{
    /**
     * The table this data is linked to. Not sure this is used tbh
     * @var string
     */
    public $parentClass;

    /**
     * The table this data is linked to
     * @var string
     */
    public $baseClass;

    /**
     * Db record Id
     * @var int
     */
    public $baseClassId;

    /**
     * Object type held in the collection.
     * @var string
     */
    public $collectionClass;

    /**
     * @var string
     */
    public $doc;

    /**
     * @var Object[]
     */
    private $items = array();

    /**
     * @var IDatabase
     */
    private $db;

    public function __construct(ReflectionProperty $prop, $baseClassId, $setCollection = true)
    {
        $this->baseClassId = (int)$baseClassId;
        $this->doc = $prop->getDocComment();
        $this->setBaseClass($prop);
        $this->setCollectionClass($prop);
        if($this->db = (new DbHandler())->getDbProvider())
        {
            if(!$this->isInitialised())
            {
                $this->setCollection();
            }
        }
    }

    /**
     * Set the class name of the related data.
     * @param ReflectionProperty $prop
     */
    protected function setCollectionClass(ReflectionProperty $prop)
    {
        if (preg_match('/@var\s+([^\s]+)/', $this->doc, $varMatches)) {
            list(, $class) = $varMatches;
            $this->collectionClass = str_replace('[]', '', $class);
        }
    }

    /**
     * Set class name of the base class
     * @param ReflectionProperty $prop
     */
    protected function setBaseClass(ReflectionProperty $prop)
    {
        $this->baseClass = $prop->getDeclaringClass()->getName();
    }

    /**
     * Get all items in the collection
     * @return Object[]
     */
    public function getCollection()
    {
        return $this->items;
    }

    /**
     * Get the related table data and add to the collection.
     */
    protected function setCollection()
    {
        //TODO: Get the mapped table name.

        $dbTable = DbModelDefinition::getTableName($this->baseClass, $this->collectionClass);
        $sql = "
            Select " . (string)$this->collectionClass .  "Id
            From " . (string)$dbTable . "
            Where " . (string)$this->baseClass . "Id = " . (int)$this->baseClassId;
        $results = $this->db->query($sql);
        $data = $results->get(1);
        if($data->hasRows())
        {
            foreach($data->getAll() as $result)
            {
                $itemId = $result[$this->collectionClass . 'Id'];
                //$obj = new $this->collectionClass($itemId);
                $obj = ModelFactory::get($this->collectionClass, $itemId);
                $this->items[$itemId] = $obj;
            }
            $t=0;
        }
    }

    /**
     * Saves each item in the collection. Possibly a dumb idea.
     */
    protected function saveMssql()
    {
        $dbTable = DbModelDefinition::getTableName($this->baseClass, $this->collectionClass);
        foreach($this->items as $key => $item)
        {
            $sql = "
                If Not Exists 
                (
                    Select * From " . $dbTable . " 
                    Where " . $this->collectionClass . "Id = " . $key . "
                )
                 Insert Into " . (string)$dbTable . "
                (". $this->baseClass . "Id, " . $this->collectionClass . "Id)
                Values (" . (int)$this->baseClassId  . ", " . (int)$key . ")
            ";
        }
    }

    /**
     * Saves each item in the collection. Possibly a dumb idea.
     */
    protected function save()
    {
        $dbTable = DbModelDefinition::getTableName($this->baseClass, $this->collectionClass);
        $sql = '';
        foreach($this->items as $key => $item)
        {
            $sql .= "
                Insert Into " . (string)$dbTable . "
                (". $this->baseClass . "Id, " . $this->collectionClass . "Id)
                Values (" . (int)$this->baseClassId  . ", " . (int)$key . ")
                Where Not Exists 
                (
                    Select * From " . $dbTable . " 
                    Where " . $this->collectionClass . "Id = " . $key . "
                    And " . $this->baseClass . "Id = " . $this->baseClassId . "
                )
            ";
        }
    }

    private function isInitialised()
    {

    }
}
