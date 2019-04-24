<?php

namespace controllers;

use Model\User;
use Vision\VisionDatabase\DbConnection;
use Vision\VisionDatabase\DbInstaller;
use Vision\VisionDatabase\Interfaces\IInstaller;
use Vision\VisionDatabase\Providers\DbProviderParameters;
use Vision\VisionFramework\Config;
use Vision\VisionFramework\Controller;
use Vision\VisionFramework\SiteScripts;
use Vision\VisionFramework\SiteStyles;
use Vision\VisionFramework\View;
use Vision\VisionModel\Model;

class SetupController extends Controller
{
    private $installer;

    protected $controllerName;

    protected $controllerMethods;

    const MSSQLLOCALDB = 'Microsoft SQL Server (LocalDb)';
    const MSSQL = 'Microsoft SQL Server';
    const MARIADB = 'MariaDb';
    const MYSQL = 'MySQL';
    const POSTGRES = 'Postgres';
    const ORACLE = 'Oracle';

    public function __construct()
    {
        $this->controllerName = str_replace('Controller', '', get_class($this));
        $this->controllerMethods = $this->getControllerMethods();
        $this->view = new View($this);

        if(!$this->siteScripts instanceof SiteScripts) {
            $this->siteScripts = new SiteScripts();
        }
        if(!$this->siteStyles instanceof SiteStyles) {
            $this->siteStyles = new SiteStyles();
        }
        $this->addDefaultScripts();
        $this->addDefaultStyles();

        $this->addPageProperties();
        $this->controllerName = $this->getControllerName();
    }

    public function indexAction()
    {
        if(!$this->isInstalled())
        {
            $db = new DbInstaller(null, true);
            $dbOptions = array();

            $viewFields['DatabaseOptions'] = $db->getAvailableInstallers();

//            foreach($inst as $in)
//            {
//                $dbOptions[$in['Provider']]
//            }
//            $viewFields['DatabaseOptions'] = array(
//                'MSSQL' => self::MSSQL,
//                'MSSQLLOCALDB' => self::MSSQLLOCALDB,
//                'MARIADB' => self::MARIADB,
//                'MYSQL' => self::MYSQL,
//                'POSTGRES' => self::POSTGRES,
//                'ORACLE' => self::ORACLE
//            );
            $this->renderView('index.phtml', $viewFields);
        }
    }

    public function post_indexAction()
    {
        $params = $_POST;
        if(array_key_exists('submitselectdb', $params))
        {
            $provParams = explode('|', $params['databaseprovider']);

            $rootDbParams = new DbProviderParameters();
            $rootDbParams->providerName = $provParams[0];
            $rootDbParams->providerType = $provParams[1];
            $installer = (new DbInstaller($rootDbParams, null))->getInstaller();

            $installer->setDbType($rootDbParams->type);

            $viewFields['databaseprovider'] = $rootDbParams->providerName;
            $viewFields['databasetype'] = $rootDbParams->getProviderType();
            $this->renderView($installer->getView('setup'), $viewFields);
        }
        elseif(array_key_exists('submitinstalldb', $params))
        {
            //$profileImage = new Image();  //TODO: This needs to be saved AFTER the db has been created
            //$profileImage->save(0);

//        $setupParams = array(
//            'WindowsUserName' => $params['windowsusername']
//        );
//        $adminUserdetails = array(
//            'Username' => $params['siteadminusername'],
//            'Password' => $params['siteadminpassword'],
//            'Firstname' => $params['siteadminfirstname'] ,
//            'Lastname' => $params['siteadminlastname'],
//            'Email' => $params['siteadminemail']
//        );
//        $setupDbDetails = array(
//            'dbHost' => $params['admindatabasehost'],
//            //'dbFilename' => $params['databasename'] . '.mdf',
//            'dbName' => 'master',
//            'dbUid' => $params['admindatabaseuid'],
//            'dbPassword' => $params['admindatabasepassword']
//        );
//        $newDbDetails = array(
//            'DbName' => $params['newdatabasename'],
//            'DbPassword' => $params['newdatabasepassword']
//        );
//
//        $setupParams['AdminUserDetails'] = $adminUserdetails;
//        $setupParams['SetupDbDetails'] = $setupDbDetails;
//        $setupParams['NewDbDetails'] = $newDbDetails;

            //$this->installer->setupInstallerParams($params);

            $adminUser = new User();
            $adminUser->Firstname               = $params['siteadminfirstname'];
            $adminUser->Lastname                = $params['siteadminlastname'];
            $adminUser->Username                = $params['siteadminusername'];
            $adminUser->Email                   = $params['siteadminemail'];
            $adminUser->setPassword($params['siteadminpassword']);

            $rootDbConnection                   = new DbConnection();
            $rootDbConnection->host             = $params['admindatabasehost'];
            $rootDbConnection->user             = $params['admindatabaseuid'];
            $rootDbConnection->password         = $params['admindatabasepassword'];
            $rootDbParams                       = new DbProviderParameters();
            if(!empty($params['databaseprovider'])) {
                $rootDbParams->providerName = $params['databaseprovider'];
            }
            $rootDbParams->providerType         = $params['databasetype'];
            $rootDbParams->connParams           = $rootDbConnection;

            $applicationDbConnection            = new DbConnection();
            $applicationDbConnection->host      = $params['admindatabasehost'];
            $applicationDbConnection->user      = 'devpondapp';
            $applicationDbConnection->name      = $params['newdatabasename'];
            $applicationDbConnection->password  = $params['newdatabasepassword'];
            $applicationDbParams                = new DbProviderParameters();
            if(!empty($params['databaseprovider']))
            {
                $applicationDbParams->providerName  = $params['databaseprovider'];
            }
            $applicationDbParams->providerType  = $params['databasetype'];
            $applicationDbParams->connParams    = $applicationDbConnection;

            $dbParams = new DbProviderParameters();
            $dbParams->providerName = $params['databaseprovider'];
            if(!empty($params['databaseprovider'])) {
                $dbParams->providerType = $params['databasetype'];
            }
            $installer = (new DbInstaller($rootDbParams))->getInstaller();
            if($installer instanceof IInstaller)
            {
                $installer->setAdminUser($adminUser);
                $installer->setRootDbParams($rootDbParams);
                $installer->setApplicationDbParams($applicationDbParams);
                $installer->setDbType($dbParams->providerType);
                if ($installer->install())
                {
                    $this->renderView($installer->getView('success'));
                }
                else
                {
                    $this->renderView($installer->getView('fail'));
                }
            }
        }
        else
        {
            $this->renderView('error.phtml');
        }
    }

    public function installdbAction()
    {
        $params = $this->requestParams;
        $model = new Model();
        $model->updateDbModelNew();
    }

    private function isInstalled()
    {
        return false;
    }

    public function configtestAction()
    {
        $config = new Config();
        $conf = array();

        $conf['name'] = 'test1';
        $conf['hostname'] = 'localhost';
        $conf['schema'] = 'test1db';
        $conf['user'] = 'testuser';
        $conf['password'] = 'testpass';
        $conf['default'] = 'true';
        $conf['provider']['name'] = 'MySQL';
        $conf['provider']['type'] = 'Standard';

        $conf['provider']['file'] = 'C:\\Users\\test.mdf';
        $config->addConfigEntry('database', $conf);

    }
    public function usertestAction()
    {
            $user = new User(1);
            $t=0;
            $user->isLoggedIn();
    }
}