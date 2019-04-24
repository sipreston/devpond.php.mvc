<?php

namespace Vision\VisionFramework;

use Vision\VisionDatabase\DbSet;
use Vision\VisionModel\Model;
use Vision\VisionModel\ModelMap;

class Startup
{
    private $baseFolder;
    private $config;
    /**
     * @var string[]
     */
    private $errors     = [];

    public function __construct()
    {
        $this->baseFolder = self::getBaseFolder();
        $this->config = new Config();
        //$this->initialiseModels();
        self::setModel();

    }

    private function initialiseModels()
    {
        $models = scandir($this->baseFolder . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR);
        $this->scanDirectory($models);
        $baseModelDir = scandir(Model::getBaseModelClassdir());
        $this->scanDirectory($baseModelDir);
    }

    private function scanDirectory($directory)
    {
        foreach($directory as $modelFile)
        {
            if(!in_array($modelFile, array('.', '..')))
            {
                $class = 'Model\\' . str_replace('.php', '', $modelFile);
                try
                {
                    $refObj = new \ReflectionClass($class);
                }
                catch(\Exception $ex)
                {
                    $this->errors[] = $ex->getMessage();
                    continue;
                }

                try
                {
                    $refObj->newInstanceWithoutConstructor();
                }
                catch(\Exception $ex)
                {
                    $this->errors[] = $ex->getMessage();
                }
            }
        }
    }

    public static function getModel()
    {
        $app = new Application('Database');
        return $app->get('DataModel');
    }

    public static function setModel()
    {
        $app = new Application('Database');
        $modelMap = new ModelMap();
        // Define your database tables here.
        // Array key is the table name
        // 'Images'         => new DbSet('Images', 'Image') for a standard table
        // 'GalleryImages'  => new DbSet('GalleryImages', 'Gallery', 'Image') for a related table

        $dataModel = array(
            'User'         => new DbSet('Users', 'User'),
            'Role'         => new DbSet('Roles', 'Role'),
            'UserRole'     => new DbSet('UserRoles', 'User', 'Role'),
            'UserSession'  => new DbSet('UserSessions', 'UserSession'),
            'Subject'       => new DbSet('Subjects', 'Subject')
            //'Images'                => new DbSet('Images','Image'),
            //'GalleryImages'         => new DbSet('GalleryImages', 'Gallery', 'Image'),
        );

        $modelMap->setMap($dataModel);
        //$app->set('DataModel', $dataModel);
    }

    public static function getBaseFolder()
    {
        return __DIR__;
    }
}