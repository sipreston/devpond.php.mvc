<?php

namespace Vision\VisionDatabase\Installers;

use Model\User;
use Vision\VisionDatabase\DbConnection;
use Vision\VisionDatabase\DbHandler;
use Vision\VisionDatabase\Interfaces\IDatabase;
use Vision\VisionDatabase\Interfaces\IInstaller;
use Vision\VisionDatabase\Providers\DbProviderParameters;

/**
 * Class InstallerMySql
 * @package Vision\VisionDatabase\Installers
 */
class InstallerMySql implements IInstaller
{
    /**
     * @var IDatabase
     */
    private $db;
    /**
     * @var DbProviderParameters
     */
    private $rootDbParams;
    /**
     * @var DbProviderParameters
     */
    private $applicationDbParams;
    /**
     * @var User
     */
    private $siteAdminUser;

    private $applicationName = 'devpondapp';
    /**
     * @var string
     */
    private $dbType;

    /**
     * Provider name
     * @const string
     */
    const PROVIDER = 'MYSQL';

    const DB_TYPE = 'Standard';
    const MYSQL = 'MYSQL';

    public function __construct(DbProviderParameters $params = null, $type = null, $initOnly = false)
    {
        if(!$initOnly)
        {
            if (isset($params)) {
                $providerParams = new DbProviderParameters();
                $providerParams->providerName = self::PROVIDER;
                $providerParams->providerType = self::DB_TYPE;
                $this->db = (new DbHandler($params))->getDbProvider();
            }
        }
    }

    /**
     * Main function to begin installation to the database
     * @return bool|User
     */
    public function install()
    {
        if($this->createDatabase())
        {
            //$this->db->disconnect(); // disconnect from master and continue with new login.
            unset($this->db);

            $this->db = (new DbHandler($this->applicationDbParams))->getDbProvider();
            if($this->db) {
                $this->createUserTables();
                if($userCreated = $this->populateUserTables())
                {
                    return $userCreated;
                    //TODO: Write the conn params to a readable file.
                }
            }
        };
        return false;
    }

    /**
     * @param $siteAdminParams
     * @param $dbDetails
     */
    public function setInstallParams($siteAdminParams, $dbDetails)
    {
        $this->siteAdminUserDetails = $siteAdminParams;
    }

    public function uninstall()
    {

    }

    /**
     * Set the Database edition to install
     * @param string $dbType
     */
    public function setDbType($dbType = null)
    {
        $this->dbType = isset($dbType) ? (string)$dbType : self::DB_TYPE;
    }

    /**
     * Start building the database in the DBMS
     * @return bool
     */
    private function createDatabase()
    {
        $dbName = (string)$this->applicationDbParams->connParams->name;
        $dbUser = (string)$this->applicationDbParams->connParams->user;
        $this->dropExistingDatabase($dbName, $dbUser);
        // Check the database hasn't already been made by the hosting service.
        $checkExists = "
            Select 
              Schema_Name 
            From Information_Schema.Schemata 
            Where Schema_Name = '" . $dbName . "'

        ";
        $dbResult = $this->db->query($checkExists, true);
        if(isset($dbResult[0][0]['Schema_Name']))
        {
            $success[0][0]['DbCreatedSuccess'] = 1;
        }
        else
        {
            $createDb = "Create Database " . $dbName . ";";
            $this->db->query($createDb, true);
            $checkDb = "Select Exists(Select Schema_Name From Information_Schema.Schemata Where Schema_Name = '" . $dbName . "') As DbCreatedSuccess
            ";

            $success = $this->db->query($checkDb, true);
        }

        if($success[0][0]['DbCreatedSuccess'] == 1)
        {
            $dbPassword = (string)$this->applicationDbParams->connParams->password;
            $applicationName = (string)$this->applicationName;

            $createUser = "
                Create User '" . $applicationName . "'@'localhost' Identified By '" . $dbPassword . "';
                Grant All Privileges On * . * To '" . $applicationName . "'@'localhost';";

            $this->db->query($createUser);
            return true;
        }

        //TODO: check database exists with login details and return true if so
        //TODO: maybe also worth checking the database doesn't already exist with login access.
        return false;
    }

    /**
     * Create the default tables
     */
    private function createUserTables()
    {
        $users = "
            Create Table Users
            (
                UserId Int Auto_Increment Not Null,
                Guid NVarChar(128) Not Null,
                Username NVarChar(255) Not Null,
                Active Bit Not Null,
                Password NVarChar(255) Not Null,
                Firstname VARCHAR(255) Null,
                Lastname VARCHAR(255) Null,
                Email VARCHAR(255) Null,
                ProfileImageId Int Null,
                DateCreated DateTime Not Null,
                DateModified DateTime Null,
                Primary Key (UserId)
            );
            Create Table Roles
            (
              RoleId Int Auto_Increment Not Null,
              RoleName NVarChar(255) Not Null,
              Primary Key (RoleId)
            );
            Create Table UserRoles
            (
              UserId Int Not Null,
              RoleId Int Not Null
            );
            Create Table UserSessions
            (
              SessionId NVarChar(128) Not Null,
              UserGuid NVarChar(128) Not Null,
              LogonTime Int Not Null,
              SessionData Text
            );";

        $this->db->query($users);
    }

    private function createSystemTables()
    {

    }

    /**
     * Create default user profile
     * @return bool|User
     */
    private function populateUserTables()
    {
        $userGuid = uniqid();
        $sql = "                
            Insert Into Users
            (
                Guid,
                Username,
                Active,
                Password,
                Firstname,
                Lastname,
                Email,
                ProfileImageId,
                DateCreated
            )
            Values
            (
                '" . $userGuid . "',
                '" . $this->siteAdminUser->Username . "',
                1,
                '" . $this->siteAdminUser->getPassword() . " ',
                '" . $this->siteAdminUser->Firstname . "',
                '" . $this->siteAdminUser->Lastname . " ',
                '" . $this->siteAdminUser->Email . " ',
                Null,
                Now()
            );
            
            Set @AdminUserId = Last_Insert_Id();
            
            Insert Into Roles
            (
                RoleName
            )
            Values
            ('Admin'),
            ('Moderator'),
            ('Standard')
            ;
            Insert Into UserRoles
            (
                UserId,
                RoleId
            )
            Values
            (
                @AdminUserId,
                (Select RoleId From Roles Where RoleName = 'Admin')
            ),
            (
                @AdminUserId,
                (Select RoleId From Roles Where RoleName = 'Moderator')
            ),
            (
                @AdminUserId,
                (Select RoleId From Roles Where RoleName = 'Standard')
            );
            
            Select 
                UserId
            From
                Users
            Where 
                Guid = '" . $userGuid . "'
        ";

        $result = $this->db->query($sql);
        if($result[0][0])
        {
            if(is_numeric($result[0][0]['UserId']))
            {
                $userId = $result[0][0]['UserId'];
                $user = new User($userId);
                if($user instanceof User) {
                    return $user;
                }
            }
        }
        return false;
    }

    public function setAdminUser(User $adminUser)
    {
        $this->siteAdminUser = $adminUser;
    }

    public function setRootDbParams(DbProviderParameters $params)
    {
        $this->rootDbParams = $params;
    }

    public function setApplicationDbParams(DbProviderParameters $params)
    {
        $this->applicationDbParams = $params;
    }

    public function setupInstallerParams($params)
    {
//        $adminUserdetails = array(
//            'Username' => $params['siteadminusername'],
//            'Password' => $params['siteadminpassword'],
//            'Firstname' => $params['siteadminfirstname'] ,
//            'Lastname' => $params['siteadminlastname'],
//            'Email' => $params['siteadminemail']
//        );
//        $setupDbDetails = array(
//            'dbHost' => $params['admindatabasehost'],
//            //'dbName' => 'master',
//            'dbUid' => $params['admindatabaseuid'],
//            'dbPassword' => $params['admindatabasepassword']
//        );
//        $newDbDetails = array(
//            'DbName' => $params['newdatabasename'],
//            'DbPassword' => $params['newdatabasepassword']
//        );

//        $setupParams['AdminUserDetails'] = $adminUserdetails;
//        $setupParams['SetupDbDetails'] = $setupDbDetails;
//        $setupParams['NewDbDetails'] = $newDbDetails;
//
//        $this->siteAdminUserDetails = $adminUserdetails;
//        $this->dbUserDetails = $newDbDetails;
//        $this->dbAdminDetails = $setupDbDetails;

        $adminUser = new User();
        $adminUser->Firstname               = $params['siteadminfirstname'];
        $adminUser->Lastname                = $params['siteadminlastname'];
        $adminUser->Username                = $params['siteadminusername'];
        $adminUser->Email                   = $params['siteadminemail'];
        $adminUser->Password                = $params['siteadminpassword'];

        $this->siteAdminUser                = $adminUser;

        $rootDbConnection                   = new DbConnection();
        $rootDbConnection->host             = $params['admindatabasehost'];
        $rootDbConnection->user             = $params['admindatabaseuid'];
        $rootDbConnection->password         = $params['admindatabasepassword'];
        $rootDbParams                       = new DbProviderParameters();
        $rootDbParams->providerName         = self::MYSQL;
        $rootDbParams->providerType         = self::DB_TYPE;
        $rootDbParams->connParams           = $rootDbConnection;
        $this->rootDbParams                 = $rootDbParams;

        $applicationDbConnection            = new DbConnection();
        $applicationDbConnection->host      = $params['admindatabasehost'];
        $applicationDbConnection->user      = $this->applicationName;
        $applicationDbConnection->name      = $params['newdatabasename'];
        $applicationDbConnection->password  = $params['newdatabasepassword'];
        $applicationDbParams                = new DbProviderParameters();
        $applicationDbParams->providerName  = self::MYSQL;
        $applicationDbParams->providerType  = self::DB_TYPE;
        $this->applicationDbParams          = $applicationDbParams;

    }

    /**
     * Get the correct the page for the installation step.
     * @param string $type
     * @return string
     */
    public function getView($type)
    {
        switch($type)
        {
            case 'success':
            {
                return 'views/setup/mysql/success.phtml';
            }
            case 'setup':
            {
                return 'views/setup/mysql/setup.phtml';
            }
            case 'fail':
            default:
                return 'views/setup/mysql/fail.phtml';
        }
    }

    /**
     * Get available installers for MySQL
     * @return DbInstallerType[]
     */
    public static function getInstallers()
    {
        $instType = new DbInstallerType();
        $instType->ProviderName = self::PROVIDER;
        $instType->DbType = 'STANDARD';
        $instType->InstallerClass = 'InstallerMySql';

        $installerTypes[$instType->DbType] = $instType;

        return $installerTypes;
    }

    /**
     * Drop the existing database. Use with caution!
     * @param string $dbName
     * @param string $userName
     */
    protected function dropExistingDatabase($dbName, $userName = null)
    {
        $dropDb = "
            Drop Database " . $dbName . ";";
        $this->db->query($dropDb);
        if(isset($userName))
        {
            $dropUser = "
                Drop User '" . $userName . "'@'localhost';";
            $this->db->query($dropUser);
        }

    }
}
