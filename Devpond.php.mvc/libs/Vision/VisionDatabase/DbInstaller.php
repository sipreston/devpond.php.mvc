<?php

namespace Vision\VisionDatabase;

use Exception, ReflectionClass;
use Vision\VisionDatabase\Exceptions\DbException;
use Vision\VisionDatabase\Interfaces\IInstaller;
use Vision\VisionDatabase\Providers\DbProviderParameters;
use Vision\VisionFramework\Config;

/**
 * Class DbInstaller
 * @package Vision\VisionDatabase
 *
 * @property $config
 * @property $connection
 * @property $provider
 * @property $installer
 * @property $connParams
 * @property $baseFolder
 * @property $installerDbParams
 */
class DbInstaller
{
    const NAMESPACE_DB_INSTALLERS = 'Vision\\VisionDatabase\\Installers\\';
    /**
     * @var Config
     */
    private $config;
    public $connection;
    public $provider;
    /**
     * @var IInstaller
     */
    private $installer;
    private $connParams = array();
    private $baseFolder;
    /**
     * @var DbProviderParameters
     */
    private $installerDbParams;

    const PROVIDER_MSSQL = "MSSQL";
    const PROVIDER_MYSQL = "MYSQL";
    const PROVIDER_POSTGRESSQL = "POSTGRES";
    const PROVIDER_ORACLE = 'ORACLE';
    const PROVIDER_MSSQL_LOCALDB = "MSSQLLOCALDB";

    public function __construct(DbProviderParameters $params = null, $initOnly = false)
    {
        $this->setPackageBaseDir();
        if(!$initOnly)
        {
            $this->config = new Config();
            if ($this->config->useDatabase)
            {
                $this->installerDbParams = $params;
//                $this->provider = $provider;
//                $this->connParams = $connParams;
                try
                {
                    $this->installer = $this->initDatabaseInstallerClass();
                }
                catch (Exception $e)
                {
                    die($e->getMessage());
                }
            }
        }
    }

    /**
     * @return IInstaller|null
     * @throws DbException
     */
    protected function initDatabaseInstallerClass()
    {
        $installer = null;
        $instClass = self::NAMESPACE_DB_INSTALLERS . 'Installer' . $this->installerDbParams->providerName;

        try
        {
            $connParams = $this->installerDbParams->connParams;
            if(isset($connParams))
            {
                $dbType = $this->installerDbParams->getProviderType();
                $installer = new $instClass($this->installerDbParams);
            }
            else
            {
                $installer = new $instClass(null, null, true);
            }
        }
        catch (Exception $ex)
        {
            throw new DbException('Failed to find installer class for ' . $this->provider . ' (' . $ex->getMessage() . ')');
        }
        return $installer;
    }

    /**
     * @return IInstaller
     */
    public function getInstaller()
    {
        return $this->installer;
    }

    public function getInstallerViewForm()
    {
        if($this->provider == self::PROVIDER_MSSQL_LOCALDB)
        {
            return 'mssql/setup_localdb.phtml';
        }
        else
        {
            return 'mssql/setup_standard.phtml';
        }
    }

    public function getAvailableInstallers()
    {
        $models = scandir($this->baseFolder . '/Installers/');
        $availableInstallers = array();
        foreach($models as $modelFile)
        {
            if(!in_array($modelFile, array('.', '..')))
            {
                $class = self::NAMESPACE_DB_INSTALLERS . str_replace('.php', '', $modelFile);
                try
                {
                    $refObj = new ReflectionClass($class);
                }
                catch(Exception $ex)
                {
                    $errors[] = $ex->getMessage();
                    $refObj = null;
                }

                if(isset($refObj))
                {
                    if ($refObj->isSubclassOf("Vision\\VisionDatabase\\Interfaces\\IInstaller"))
                    {
                        $installerTypes = $class::getInstallers();

                            foreach($installerTypes as $inType)
                            {
                                $instDetail = array(
                                    'Provider' => $inType->ProviderName,
                                    'Type' => $inType->DbType,
                                    'Description' => $inType->Description,
                                    'ClassName' => $class
                                );
                                $availableInstallers[] = $instDetail;
                            }

//                        else
//                        {
//                            $instDetail = array(
//                                'Provider' => $installerTypes['Provider'],
//                                'Type' => $installerTypes['Type'],
//                                'ClassName' => $class
//                            );
//                            $availableInstallers[] = $instDetail;
//                        }

//                        try {
//                            $refObj->newInstanceWithoutConstructor();
//                        } catch (Exception $ex) {
//                            $errors[] = $ex->getMessage();
//                        }
                    }
                }
            }
        }
        return $availableInstallers;
    }

    private function setPackageBaseDir()
    {
        $this->baseFolder = Util::getPackageBaseDir();
    }
}