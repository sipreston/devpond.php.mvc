<?php

namespace Vision\VisionDatabase\Providers;

use Vision\VisionDatabase\Interfaces\IDatabase;

class MsSql implements IDatabase
{
    private $connection;
    private $connected;
    private $connDetails;
    private $dbType = 'STANDARD';
    private $dbHost;

    const CONNECT_RETRIES = 5;

    public function __construct($connParams = array(), $dbType = null)
    {
        if(isset($dbType)) {
            $this->dbType = $dbType;
        }
        if(!empty($connParams))
        {
            $this->setConnection($connParams);
        }
    }

    public function setConnection(DbProviderParameters $params)
    {
        switch($this->dbType)
        {
            case 'LOCALDB':
            {
                $this->setLocalDbConnection($params);
                break;
            }
            default:
                $this->setStandardConnection($params);
                break;
        }
    }

    protected function setLocalDbConnection($params = array())
    {
        $this->dbHost = $params['dbHost'];
        $db_filename = $params['dbFilename'];
        $db_name = $params['dbName'];
        $db_uid = $params['dbUid'];
        $db_pass = $params['dbPassword'];
        $this->connDetails = array('AttachDBFileName' => $db_filename, 'Database' => $db_name, 'UID' => $db_uid, 'PWD' => $db_pass);
    }

    protected function setStandardConnection($params = array())
    {
        $this->dbHost = $params['dbHost'];
        $db_name = $params['dbName'];
        $db_uid = $params['dbUid'];
        $db_pass = $params['dbPassword'];
        $this->connDetails = array('Database' => $db_name, 'UID' => $db_uid, 'PWD' => $db_pass);
    }

    public function connect()
    {
        for ($connectAttempts = 0; $connectAttempts < self::CONNECT_RETRIES; $connectAttempts++) {
            $this->connection = sqlsrv_connect($this->dbHost, $this->connDetails);
            if ($this->connection) {
                break;
            }
        }
        if(!$this->connection)
        {
            $errors = sqlsrv_errors();
            die("Error connecting to the database: " . sqlsrv_errors());
        }
        else {
            $this->connected = true;
        }
    }

    public function query($sqlQuery)
    {
        if(!$this->connected)
        {
            $this->connect();
        }
        $result = sqlsrv_query($this->connection, $sqlQuery);
        $results = Array();
        if ($result)
        {
            $data = array();
            $i = 0;
            do
            {
                while ($row = sqlsrv_fetch_array($result)) {
                    $data[] = $this->SqlSrvFillArray($row);
                }
                if(count($data) > 0) {
                    $results[$i] = $data;
                    unset($data);
                    $i++;
                }
            }
            while (sqlsrv_next_result($result));
        }
        else
        {
            //TODO: Log these somewhere
            //$t = sqlsrv_errors($result);
        }
        $errors[] = sqlsrv_errors();
        if($errors != false && count($errors) > 0)
        {
            $results['errors'] = $errors;
        }


        return $results;
    }

    public function getColumns($table)
    {
//        $sql = "
//            Select Column_Names
//            From
//              Information_Schema.Columns
//              Where TableSchema = '" . $this->dbName . "'
//              And TableName = '" . $table . "'
//        ";
        $sql = "
            Select 
                Column_Name,
                Data_Type,
                Is_Nullable,
                Character_Maximum_Length
            
            From 
                Information_Schema.Columns
                Where Table_Catalog = '" . $this->dbName . "'
                And Table_Name = '" . $table . "'
        ";
        $results = $this->query($sql);
        $cols = array();
        foreach($results[0] as $result)
        {
            $col = new DbColumnDefinition();
            $col->columnName = $result['Column_Name'];
            $col->type = $result['Type'];
            $col->length = $result['Type'];
            $col->mappedType = $col->mapType($result['Type']);
            $col->isIdentity = ($result['Key'] == 'PRI') ? true : false;
            $col->isPrimaryKey = ($result['Key'] == 'PRI') ? true : false;
            $col->autoIncrement = (strpos($result['Extra'], 'auto_increment') !== false ) ? true : false;
            $col->isNullable = ($result['Null'] == 'YES') ? true : false;
            $col->isForeignKey = true;
            $col->isUnique = ($result['Key'] == 'MUL' || $col->isPrimaryKey == true) ? true : false;
        }
        return $cols;
    }

    public function executeProcedure($proc = array())
    {
        // TODO: Implement executeProcedure() method.
    }

    private function SqlSrvFillArray($row)
    {
        $results = array();
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                $results[$key] = $value;
            }
        }
        return $results;
    }

    public function setConn($dbSettings)
    {
        if(strtoupper($dbSettings['dbType']) == 'LOCALDB')
        {
            $connParams = array(
                'dbHost' => $dbSettings['dbHost'],
                'dbName' => $dbSettings['dbName'],
                'dbUid' => $dbSettings['dbUser'],
                'dbPassword' => $dbSettings['dbPassword'],
                'dbFilename' => $dbSettings['dbFilename'],
            );
            $this->dbType = 'LOCALDB';
        }
        else
        {
            $connParams = array(
                'dbHost' => $dbSettings['dbHost'],
                'dbName' => $dbSettings['dbName'],
                'dbUid' => $dbSettings['dbUser'],
                'dbPassword' => $dbSettings['dbPassword'],
            );

        }

        $this->setConnection($connParams);
        return $connParams;
    }

    public function getTables($dbName = null)
    {
        $tables = array();
        $sql = "
            Select 
              Table_Name
            From 
              Information_Schema.Tables
        ";

        if(isset($dbName))
        {
            $sql .= "
                Where
                    Table_Schema = '" . $dbName . "'
            ";
        }

        $results = $this->query($sql);
        if($results)
        {
            foreach($results[0] as $result)
            {
                $tables[] = $result['Table_Name'];

            }
        }
        return $tables;
    }

    public function saveTable($tableName, $tableDefinition)
    {

    }

    public function isConnectionSet()
    {
        // TODO: Implement isConnectionSet() method.
    }

    public function disconnect()
    {
        if($this->connection)
        {
            sqlsrv_close($this->connection);
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}