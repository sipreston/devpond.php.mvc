<?php
/*
 * @deprecated Use DbHandler class
 */
class DatabaseHandler
{
    public $config;

    public $connection;

    public $provider;

    public function __construct()
    {
        $this->config = new Config();
        if($this->config->useDatabase)
        {
            $this->provider = $this->config->databaseProvider;
            $this->plugin = $this->config->databasePlugin;
            $this->setConnection($this->provider);
        }
    }

    public function setConnection($provider)
    {
        switch ($provider) {
            case "MYSQL":
                $this->SetMySQLConnection();
                break;
            case "MSSQL":
                $this->SetMSSQLConnection();
                break;
            case "MSSQLLOCALDB":
                $this->SetMSSQLLocalDbConnection();
                break;
            case "ORACLE":
                $this->SetOracleConnection();
                break;
            case "POSTGRESQL":
                $this->SetPostgreSQLConnection();
                break;
        }
    }

    private function SetMySQLConnection()
    {
        $db['db_host'] = "localhost";
        $db['db_user'] = "root";
        $db['db_pass'] = "";
        $db['db_name'] = "cms";

        foreach ($db as $key => $value) {
            define(strtoupper($key), $value);
        }
        $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if (!$this->connection) {
            die("Error connecting to the database: " . mysqli_error($this->connection));
        }
    }

    //function SetMSSQLConnection()
    //{
    //    $DB_HOST = "(localdb)\MSSQLLocalDB";
    //    $conn = array(
    //        UID => "CMS",
    //        PWD => "CMS",
    //        Database => "cms",
    //    );
    //
    //    $connection = sqlsrv_connect($DB_HOST, $conn);
    //
    //    if (!$connection) {
    //        $something = sqlsrv_errors();
    //        die("Error connecting to the database: " . sqlsrv_errors());
    //    }
    //
    //    $sql = "SELECT * FROM Test";
    //    $test = sqlsrv_query($connection, $sql);
    //    $GLOBALS['connection'] = $connection;
    //}
    private function SetMSSQLConnection()
    {
        $db_host = 'SIMON-Q6600\SQLEXPRESS';
        $db_name = 'cms';
        $db_uid = "CMS";
        $db_pass = "CMS";
        $conn = array('Database' => $db_name, 'UID' => $db_uid, 'PWD' => $db_pass);

        $this->connection = sqlsrv_connect($db_host, $conn);
        if (!$this->connection) {
            $something = sqlsrv_errors();
            die("Error connecting to the database: " . sqlsrv_errors());
        }

        $sql = "SELECT * FROM Test";
        $result = sqlsrv_query($this->connection, $sql);
        $results = array_fill_keys(array('Name', 'Message'), '');
        $results = Array();
        $i = 0;
        while ($row = sqlsrv_fetch_array($result)) {
            $tmp = array_fill_keys(array('Name', 'Message'), '');
            $tmp['Name'] = $row['Name'];
            $tmp['Message'] = $row['Message'];
            $results[$i] = $tmp;
            $i++;
        }
    }

    private function SetMSSQLLocalDbConnection()
    {
        $db_host = '(localdb)\\MSSQLLocalDB';
        $db_filename = 'C:\\Users\\Simon\\cms.mdf';
        $db_name = 'cms';
        $db_uid = "CMS";
        $db_pass = "CMS";
        $conn = array('AttachDBFileName' => $db_filename, 'Database' => $db_name, 'UID' => $db_uid, 'PWD' => $db_pass);

        $this->connection = sqlsrv_connect($db_host, $conn);
        if (!$this->connection) {
            die("Error connecting to the database: " . print_r(sqlsrv_errors()));
        }
    }

    private function SetOracleConnection()
    {


    }

    private function SetPostgreSQLConnection()
    {

    }

    public function getConnection()
    {
        return $this->connection;
    }
}
