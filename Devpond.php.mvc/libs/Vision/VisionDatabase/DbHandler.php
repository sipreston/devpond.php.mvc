<?php

namespace Vision\VisionDatabase;

use Exception;
use Vision\VisionDatabase\Exceptions\DbException;
use Vision\VisionDatabase\Interfaces\IDatabase;
use Vision\VisionDatabase\Providers\DbProviderParameters;
use Vision\VisionFramework\Application;
use Vision\VisionFramework\Config;

/**
 * Class DbHandler
 * @package Vision\VisionDatabase
 *
 * @property $config
 * @property $provider
 * @property $dbType
 * @property $db
 */
class DbHandler
{
    const NAMESPACE_DB_PROVIDERS = 'Vision\\VisionDatabase\\Providers\\';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $provider;

    /**
     * @var string
     */
    private $dbType;

    /**
     * @var IDatabase
     */
    private $db;

    private $connParams = array();

    const PROVIDER_MSSQL = "MSSQL";
    const PROVIDER_MYSQL = "MYSQL";
    const PROVIDER_POSTGRESSQL = "POSTGRES";
    const PROVIDER_ORACLE = 'ORACLE';
    const PROVIDER_MSSQL_LOCALDB = "MSSQLLOCALDB";

    public function __construct(DbProviderParameters $params = null, $useDatabase = null)
    {
        $this->config = new Config();
        if($this->config->useDatabase || $useDatabase = true)
        {
            if(isset($dbName))
            {
                if(array_key_exists($this->config->availableDatabases, $dbName)) {
                    $selectedDb = $this->config->availableDatabases->{$dbName};
                }
                else {
                    $selectedDb = $this->getDefaultDb($this->config->availableDatabases);
                }
            }
            elseif(isset($params))
            {
                $selectedDb = $params;
//                $selectedDb = array(
//                    'dbName'        => $conn->name,
//                    'dbUser'        => $conn->user,
//                    'dbHost'        => $conn->host,
//                    'dbPassword'    => $conn->password
//                );
            }
            else
            {
                $selectedDb = $this->getDefaultDb($this->config->availableDatabases);
            }

            //$this->connParams = $this->setConnParams($selectedDb);
            $this->provider = isset($provider) ? $provider : $selectedDb->providerName;

            $this->dbType = $selectedDb->getProviderType();

            //$this->connParams = isset($connParams) ? $connParams : $this->config->connParams;
            //$this->dbType = $dbType;
            try {
                $db = $this->initDatabaseClass();
            }
            catch(DbException $e) {
                die($e->getMessage());
            }
            if($db instanceof IDatabase)
            {
                if(!$db->isConnectionSet()) {
                    $db->setConnection($selectedDb);
                }
                $this->db = $db;
            }
        }
    }

    /**
     * Get the required provider.
     * @return IDatabase|null
     * @throws DbException
     */
    protected function initDatabaseClass()
    {
        $db = null;
        $provider = $this->provider;

        try
        {
            $app = new Application('Database');
            if($providers = $app->get('Providers'))
            {
                //return $providers;
                //return $db;
            }
            $provider = self::NAMESPACE_DB_PROVIDERS . $provider;
            $db = new $provider();
        }
        catch(Exception $ex)
        {
            throw new DbException("Failed to find " . $this->provider . " provider. " . $ex->getMessage());
        }

        if($db instanceof IDatabase)
        {
            //$app->add('Providers', $db);
            return $db;
        }

        return null;
    }

    /**
     * @return IDatabase|null
     */
    public function getDbProvider()
    {
        return $this->db;
    }

    /**
     * @param $databases
     * @return null
     */
    protected function getDefaultDb($databases)
    {
        $defaultDb = null;
        foreach($databases as $database)
        {
            $default = $database->connParams->isDefault;
            if($default == 'true')
            {
                $defaultDb = $database;
                break;
            }
        }

        return $defaultDb;
    }
}