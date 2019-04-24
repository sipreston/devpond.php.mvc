<?php

namespace Vision\VisionFramework;

use Vision\VisionDatabase\DbConnection;
use Vision\VisionDatabase\Providers\DbProviderParameters;

class Config
{
    public $databaseProviders = array();
    public $databasePlugin;
    public $useDatabase = true;
    private $settings = array();
    public $availableDatabases = array();

    private $installed;
    const CONFIG_FILE = 'Config.xml';

    public function __construct()
    {
        //include $_SERVER['DOCUMENT_ROOT'] . '/config.php';

        $this->readConfigFileXml();
//        $this->databaseProvider = $GLOBALS['config']['DatabaseSettings']['DATABASE_PROVIDER'];
//        $this->databasePlugin = $GLOBALS['config']['DatabaseSettings']['SQL_PLUGIN'];
//        $this->useDatabase = $this->checkDbUseValue($GLOBALS['config']['DatabaseSettings']['USE_DATABASE']);
    }

    public function checkDbUseValue($value)
    {
        if ($value == true || $value == 1 || strtolower($value) == 'on') {
            return true;
        } elseif ($value == false || $value == 0 || strtolower($value) == 'off') {
            return false;
        } else {
            return false;
        }
    }

    private function readConfigFileXml()
    {
        $xmlFile = $_SERVER['DOCUMENT_ROOT'] . '/' . self::CONFIG_FILE;
        if(!file_exists($xmlFile))
            return;

        if ($file = simplexml_load_file($xmlFile)) {
            foreach($file->database as $db) {
                $this->setAvailableDatabases($db);
            }
            $this->installed = ($file->installed == 1) ? true : false;
        }
    }

    private function setAvailableDatabases(\SimpleXMLElement $database)
    {
        $db = new DbProviderParameters();
        $db->providerName = (string)$database->provider->name;

        $conn = new DbConnection();
        $conn->name = (string)$database->schema;
        $conn->host = (string)$database->hostname;
        $conn->user = (string)$database->user;
        $conn->password = (string)$database->password;
        if(isset($database->provider->type))
        {
            $db->providerType = (string)$database->provider->type;
        }
        if(isset($database->provider->file))
        {
            $conn->fileName = (string)$database->provider->file;
        }
        if(isset($database->default))
        {
            $default = (bool)$database->default;
            $conn->isDefault = $default;
        }

        $db->connParams = $conn;


        $dbIdentifier = (string)$database->name;
//        $db['dbName'] = (string)$database->schema;
//        $db['dbHost'] = (string)$database->hostname;
//        $db['dbUser'] = (string)$database->user;
//        $db['dbPassword'] = (string)$database->password;
//        $db['dbProvider'] = (string)$database->provider->name;
//        if(isset($database->provider->type))
//        {
//            $db['dbType'] = (string)$database->provider->type;
//        }
//        if(isset($database->provider->file))
//        {
//            $db['dbFilename'] = (string)$database->provider->file;
//        }
//        if(isset($database->default))
//        {
//            $default = (string)$database->default;
//            $db['isDefault'] = $default;
//        }

        $this->availableDatabases[$dbIdentifier] = $db;
    }

    public function addConfigEntry($entryType, $params)
    {
        $xmlFile = $_SERVER['DOCUMENT_ROOT'] . '/' . self::CONFIG_FILE;
        if ($file = simplexml_load_file($xmlFile))
        {
            $newEntry = $file->addChild($entryType);
            $xmlElement = $this->mapXmlArrayElements($newEntry, $params);
            $file->asXML($xmlFile);

        }
        $t=0;
    }

    private function mapXmlArrayElements(\SimpleXMLElement $newEntry, $params)
    {
        foreach($params as $key => $param)
        {
            if(is_array($param))
            {
                $child = $newEntry->addChild($key);
                $this->mapXmlArrayElements($child, $param);
            }
            else
            {
                $newEntry->addAttribute($key, $param);
            }
            //$file->config->{$entryType}->addChild($key, $param);
        }
        return $newEntry;
    }

    public function isInstalled()
    {
        return $this->installed == true ? true : false;
    }

    public function useDatabase()
    {
        if($this->isInstalled())
        {
            return $this->useDatabase == true ? true : false;
        }
    }
}