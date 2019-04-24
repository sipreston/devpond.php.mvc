<?php

namespace Vision\VisionDatabase;

use Vision\VisionFramework\Startup;

/**
 * Class DbModelDefinition
 * @package Vision\VisionDatabase
 */
class DbModelDefinition
{
    /**
     * @var array
     */
    private $modelDefinition = array();

    public function __construct()
    {
        $this->setModel();
    }

    /**
     * @return null
     */
    public static function getModel()
    {
        return Startup::getModel();
    }

    /**
     *
     */
    private function setModel()
    {
        Startup::setModel();
        $this->modelDefinition = $this::getModel();
    }

    private function getDefinedModel()
    {
        // Define your database tables here.
        // Array key is the table name
//        $definedModel = array(
//            'Subjects' => new DbTableDefinition('Subject', $this),
//            'Videos' => new DbTableDefinition('Video', $this),
//            'Images' => new DbTableDefinition('Image', $this),
//            'SubjectVideos' => new DbRelatedTable('Subject', 'Video' ),
//            'SubjectImages' => new DbRelatedTable('Subject', 'Image'),
//            'Files' => new DbTableDefinition('File', $this),
//            'VideoFiles' => new DbRelatedTable('Video', 'File'),
//        );
//        return $definedModel;
//        //TODO: Database map will be defined in Startup class.
//        //$this->modelDefinition = Startup::defineModel();
    }

    public function get()
    {
        if(isset($this->modelDefinition))
        {
            $this->setModel();
        }
        return $this->modelDefinition;
    }

    public static function getTableName($classA, $classB)
    {
        $dataModel = self::getModel();
        foreach($dataModel as $table)
        {
            if($table instanceof DbSet) {
                if ($table->isMappedTable($classA, $classB)) {
                    return $table->getTableName();
                }
            }
        }
        return null;
    }
}