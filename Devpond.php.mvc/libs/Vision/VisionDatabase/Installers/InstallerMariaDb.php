<?php

namespace Vision\VisionDatabase\Installers;

use Model\User;
use Vision\VisionDatabase\DbHandler;
use Vision\VisionDatabase\Interfaces\IDatabase;
use Vision\VisionDatabase\Interfaces\IInstaller;
use Vision\VisionDatabase\Providers\DbProviderParameters;

/**
 * Class InstallerMariaDb
 * @package Vision\VisionDatabase\Installers
 *
 * @property $dbType
 */
class InstallerMariaDb implements IInstaller
{
    /**
     * @var IDatabase
     */
    private $db;

    /**
     * @var string
     */
    private $dbType;

    /**
     * @var array
     */
    private $dbAdminDetails = array();

    /**
     * @var array
     */
    private $dbUserDetails = array();

    /**
     * @var array
     */
    private $siteAdminUserDetails = array();

    /**
     * @var array
     */
    private $installParams = array();

    /**
     * @var string
     */
    private $databaseName;

    /**
     * Provider name
     * @const string
     */
    const PROVIDER = 'MariaDb';

    /**
     * @const string
     */
    const DB_TYPE = 'STANDARD';

    public function __construct(DbProviderParameters $params = null, $type = null, $initOnly = false)
    {
        $this->dbType = 'MSSQL' . $type;
        if(isset($params))
        {
//            $this->siteAdminUserDetails = $params['AdminUserDetails'];
//            $this->dbUserDetails = $params['NewDbDetails'];
//
//            // Initial DB connection, to create the database, is the same for normal SQL Server or LocalDb
//            $this->dbAdminDetails = $params['SetupDbDetails'];
            $this->db = (new DbHandler(self::MSSQL, $this->dbAdminDetails, true))->getDbProvider();
        }
    }

    public function install()
    {
        if($this->createDatabase())
        {
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

            $this->db = (new DbHandler(self::MSSQL . self::MSSQLLOCALDB, $this->dbUserDetails))->getDbProvider();
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
        // Check the database hasn't already been made by the hosting service.
        $checkExists = "
            Select 
              Schema_Name 
            From Information_Schema.Schemata 
            Where Scheme Name = '" . $this->dbUserDetails['DbName'] . "'

        ";
        $dbResult = $this->db->query($checkExists);

        if($dbResult[0][''] == strtolower($this->dbUserDetails['DbName']))
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
                UserGuid NVarChar(128) Not Null,
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
        if($this->dbType = 'MSSQLLOCALDB')
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
                if($this->dbType == 'MSSQLLOCALDB') {
                    return 'views/setup/mssql/success_localdb.phtml';
                }
                else {
                    return 'views/setup/mssql/success_standard.phtml';
                }

            }
            case 'setup':
            {
                if($this->dbType == 'MSSQLLOCALDB') {
                    return 'views/setup/mssql/setup_localdb.phtml';
                }
                else {
                    return 'views/setup/mssql/setup_standard';
                }

            }
            case 'fail':
            default:
                return 'views/setup/msssql/fail.phtml';
        }
    }

    public static function getInstallers()
    {
        $instType = new DbInstallerType();
        $instType->ProviderName = self::PROVIDER;
        $instType->DbType = 'STANDARD';
        $instType->InstallerClass = 'InstallerMariaDb';

        $installerTypes[$instType->DbType] = $instType;

        return $installerTypes;
    }
}