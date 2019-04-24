<?php

namespace Vision\VisionDatabase\Installers;

use Model\User;
use Vision\VisionDatabase\DbHandler;
use Vision\VisionDatabase\Interfaces\IInstaller;
use Vision\VisionDatabase\Providers\DbProviderParameters;
use Vision\VisionFramework\Config;

class InstallerMsSql implements IInstaller
{
    private $db;
    private $dbType;
    private $dbAdminDetails = array();
    private $dbUserDetails = array();
    private $siteAdminUserDetails = array();
    private $installParams = array();
    private $databaseName;

    const PROVIDER = 'MSSQL';
    const MSSQLLOCALDB = 'LOCALDB';
    const MSSQL = 'MSSQL';
    const MDF_SUFFIX = '.mdf';

    const DB_TYPE = 'Standard';
    public function __construct(DbProviderParameters $params = null, $type = null, $initOnly = false)
    {
        if(!$initOnly)
        {
            $this->dbType = $type;
            if (isset($params)) {
                $this->setupInstallerParams($params);
//            $this->siteAdminUserDetails = $params['AdminUserDetails'];
//            $this->dbUserDetails = $params['NewDbDetails'];
//
//            // Initial DB connection, to create the database, is the same for normal SQL Server or LocalDb
//            $this->dbAdminDetails = $params['SetupDbDetails'];
                $this->db = (new DbHandler(null,self::MSSQL, $this->dbAdminDetails, true))->getDbProvider();
            }
        }
    }

    public function install()
    {
        if($this->createDatabase())
        {
            //create new Database entry in Config.xml
            $config = new Config();
            // this will do for now. I hope.
            $type = isset($this->dbType) ? $this->dbType : 'STANDARD';
            $provider = array(
                'name' => 'MSSQL',
                'type' => $type,

            );
            $conf = array();

            $conf['name'] = $this->dbUserDetails['DbName'];
            $conf['hostname'] = $this->dbUserDetails['DbHost'];
            $conf['schema'] = $this->dbUserDetails['DbName'];
            $conf['user'] = $this->dbUserDetails['dbUserName'];
            $conf['password'] = $this->dbUserDetails['DbPassword'];
            $conf['default'] = isset($this->installParams['IsDefault']) ? $this->installParams['IsDefault'] : 'true';
            $conf['provider']['name'] = self::PROVIDER;
            $conf['provider']['type'] = $this->dbType;
            if($this->dbType == self::MSSQLLOCALDB) {
                $conf['provider']['file'] = 'C:\\Users\\' . $this->installParams['WindowsUserName'] . '\\' . $this->dbUserDetails['DbName'] . self::MDF_SUFFIX;
            }
            $config->addConfigEntry('database', $conf);
            $this->db->disconnect(); // disconnect from master and continue with new login.
            unset($this->db);
            $dbUserDetails = array(
                'dbHost' => '(localdb)\\MSSQLLocalDB',
                //'dbHost' => $this->dbAdminDetails['dbHost'],
                'dbName' => $this->dbUserDetails['DbName'],
                'dbUid' => 'cms_app',
                'dbPassword' => $this->dbUserDetails['DbPassword'],
                'dbFilename' => 'C:\\Users\\' . $this->installParams['WindowsUserName'] . '\\' . $this->dbUserDetails['DbName'] . self::MDF_SUFFIX
            );
            $this->dbUserDetails = $dbUserDetails;

            $this->db = (new DbHandler(null, $this->dbType, $this->dbUserDetails))->getDbProvider();
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

    public function setInstallParams($siteAdminParams, $dbDetails)
    {
        $this->siteAdminUserDetails = $siteAdminParams;
    }

    public function uninstall()
    {

    }

    private function createDatabase()
    {
        if($this->dbType == self::MSSQLLOCALDB)
        {
            $createSql = "
                Create Database [" . $this->dbUserDetails['DbName'] . "]
                On Primary
                (
                    Name = " . $this->dbUserDetails['DbName'] . ",
                    Filename = 'C:\Users\\" . $this->installParams['WindowsUserName'] . "\\" . $this->dbUserDetails['DbName'] . ".mdf'
                )
                Log On
                (
                    Name = '" . $this->dbUserDetails['DbName'] . "_log.ldf',
                    Filename = 'C:\Users\\" . $this->installParams['WindowsUserName'] . "\\" . $this->dbUserDetails['DbName'] . "_log.ldf'
                )
                
                
                If(Exists(
                    Select name From master.dbo.sysdatabases
				    Where ('[' + name + ']' = '" . $this->dbUserDetails['DbName'] . "'
				    Or name = '" . $this->dbUserDetails['DbName'] . "')
                ))
					Select 1 As DbCreatedSuccess
				Else
					Select 0 As DbCreatedSuccess
                ";
        }
        else
        {
            $createSql = "";
        }
        $success = $this->db->query($createSql);
        if($success[0][0]['DbCreatedSuccess'] == 1)
        {
            $applicationName = 'cms_app';
            // create the login details that the software uses to connect to the db.
            $createLoginSql = "
                CREATE LOGIN " . $applicationName . " WITH PASSWORD = '" . $this->dbUserDetails['DbPassword'] . "';
                
                Use " . $this->dbUserDetails['DbName'] . ";
                
                IF NOT EXISTS (SELECT * FROM sys.database_principals WHERE name = N'" . $applicationName . "')
                BEGIN
                    CREATE USER [" . $applicationName . "] FOR LOGIN [" . $applicationName . "]
                    EXEC sp_addrolemember N'db_owner', N'" . $applicationName . "'
                END;
                
                Alter Server Role [dbcreator] Add MEMBER [cms_app];
            ";

            $loginSuccess = $this->db->query($createLoginSql);

            return true;
        }

        //TODO: check database exists with login details and return true if so
        //TODO: maybe also worth checking the database doesn't already exist with login access.
        return false;
    }

    private function createUserTables()
    {
        $sql = "
            Create Table Users
            (
                UserId Int Identity(1,1) Primary Key Not Null,
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
            )
            
            Create Table Roles
            (
              RoleId Int Identity(1,1) Primary Key Not Null,
              RoleName NVarChar(255) Not Null
            )
            
            Create Table UserRoles
            (
              UserId Int Not Null,
              RoleId Int Not Null
            )
            
            Create Table UserSessions
            (
              SessionId NVarChar(128) Not Null,
              UserGuid NVarChar(128) Not Null,
              LogonTime Int Not Null,
              SessionData NVarChar(Max)
            );
        ";

        $result = $this->db->query($sql);
    }

    private function createSystemTables()
    {

    }

    private function populateUserTables()
    {
        $userGuid = uniqid();
        $sql = "
            Declare 
                @AdminUserId Int
                
            Insert Into [Users]
            (
                [UserGuid],
                [Username],
                [Active],
                [Password],
                [Firstname],
                [Lastname],
                [Email],
                [ProfileImageId],
                [DateCreated]
            )
            Values
            (
                '" . $userGuid . "',
                N'" . $this->siteAdminUserDetails['Username'] . "',
                1,
                N'" . $this->siteAdminUserDetails['Password'] . " ',
                N'" . $this->siteAdminUserDetails['Firstname'] . "',
                N'" . $this->siteAdminUserDetails['Lastname'] . " ',
                N'" . $this->siteAdminUserDetails['Email'] . " ',
                Null,
                GetDate()
            )
            
            Select @AdminUserId = SCOPE_IDENTITY()
            
            Insert Into [Roles]
            (
                RoleName
            )
            Values
            ('Admin'),
            ('Moderator'),
            ('Standard')
            ;
            Insert Into [UserRoles]
            (
                [UserId],
                [RoleId]
            )
            Values
            (
                @AdminUserId,
                (Select [RoleId] From [Roles] Where [RoleName] = 'Admin')
            ),
            (
                @AdminUserId,
                (Select [RoleId] From [Roles] Where [RoleName] = 'Moderator')
            ),
            (
                @AdminUserId,
                (Select [RoleId] From [Roles] Where [RoleName] = 'Standard')
            );
            
            Select 
                UserId
            From
                Users
            Where 
                UserGuid = '" . $userGuid . "'
        ";

        $result = $this->db->query($sql);
        if($result[0][0])
        {
            if(is_numeric($result[0][0]['UserId']))
            {
                $userId = $result[0][0]['UserId'];
                $user = (new User($userId))->get();
                if($user instanceof User) {
                    return $user;
                }
            }
        }
        return false;
    }
    public function setupInstallerParams($params)
    {
        $setupParams = array();
        if($this->dbType = self::MSSQLLOCALDB)
        {
            $setupParams['WindowsUserName'] = $params['windowsusername'];
        }
        $adminUserdetails = array(
            'Username' => $params['siteadminusername'],
            'Password' => $params['siteadminpassword'],
            'Firstname' => $params['siteadminfirstname'] ,
            'Lastname' => $params['siteadminlastname'],
            'Email' => $params['siteadminemail']
        );
        $setupDbDetails = array(
            'dbHost' => $params['admindatabasehost'],
            'dbName' => 'master',
            'dbUid' => $params['admindatabaseuid'],
            'dbPassword' => $params['admindatabasepassword']
        );
        $newDbDetails = array(
            'DbName' => $params['newdatabasename'],
            'DbPassword' => $params['newdatabasepassword']
        );

        $setupParams['AdminUserDetails'] = $adminUserdetails;
        $setupParams['SetupDbDetails'] = $setupDbDetails;
        $setupParams['NewDbDetails'] = $newDbDetails;

        $this->siteAdminUserDetails = $adminUserdetails;
        $this->dbUserDetails = $newDbDetails;
        $this->dbAdminDetails = $setupDbDetails;

        $this->installParams = $setupParams;
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

    public function setDbType($dbType = null)
    {
        $this->dbType = isset($dbType) ? (string)$dbType : self::DB_TYPE;
    }

    public function getView($type)
    {
        switch($type)
        {
            case 'success':
            {
                if($this->dbType == self::MSSQLLOCALDB) {
                    return 'views/setup/mssql/success_localdb.phtml';
                }
                else {
                    return 'views/setup/mssql/success_standard.phtml';
                }

            }
            case 'setup':
            {
                if($this->dbType == self::MSSQLLOCALDB) {
                    return 'views/setup/mssql/setup_localdb.phtml';
                }
                else {
                    return 'views/setup/mssql/setup_standard.phtml';
                }
            }
            case 'fail':
            default:
                return 'views/setup/mssql/fail.phtml';
        }
    }

    public static function getInstallers()
    {
        $instTypeSt = new DbInstallerType();
        $instTypeSt->ProviderName = self::PROVIDER;
        $instTypeSt->DbType = 'STANDARD';
        $instTypeSt->InstallerClass = 'InstallerMySql';
        $instTypeSt->Description = 'Standard Installations';
        $installerTypes[$instTypeSt->DbType] = $instTypeSt;

        $instTypeLdb = new DbInstallerType();
        $instTypeLdb->ProviderName = self::PROVIDER;
        $instTypeLdb->DbType = self::MSSQLLOCALDB;
        $instTypeLdb->InstallerClass = 'InstallerMySql';
        $instTypeLdb->Description = 'LocalDb Development';
        $installerTypes[$instTypeLdb->DbType] = $instTypeLdb;

        return $installerTypes;
    }
}