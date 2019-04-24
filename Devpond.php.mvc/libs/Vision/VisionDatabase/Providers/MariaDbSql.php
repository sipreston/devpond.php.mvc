<?php

namespace Vision\VisionDatabase\Providers;

use Vision\VisionDatabase\Interfaces\IDatabase;

class MariaDbSql extends MySql implements IDatabase
{
    public function __construct($connParams)
    {
        parent::__construct();
    }
    public function setConnection($connParams = array())
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

    public function setConn($dbSettings)
    {
        $connParams = array(
            'dbHost' => $dbSettings['dbHost'],
            'dbName' => $dbSettings['dbName'],
            'dbUid' => $dbSettings['dbUser'],
            'dbPassword' => $dbSettings['dbPassword'],
        );
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

        $results = $this->query($sql, true);
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
        $existingCols = $this->getColumns($tableName);
        if(count($existingCols) > 0)
        {
            $sql = "
                Alter Table " . $tableName;
            foreach($tableDefinition as $def)
            {
                if(in_array($def['']))
                {

                }
            }

            $sql .= "Add Column ";

            "";
        }
        else
        {
            $fkeys = array();
            $sql = "
                Create Table " . $tableName . "
                (
            ";
            foreach($tableDefinition as $def)
            {
                $sql .= $def['Column'] . " ";
                if($def['IsForeignKey'] == false) {
                    $sql .= $this->getDataTypeFromVariable($def['DataType']) . " ";
                }
                else
                {
                    $sql .= "Int ";
                }
                if($def['IsIdentity'] == true)
                {
                    $sql .= "Auto_Increment ";
                    $pKey = $def['Column'];
                }
                if($def['IsNullable'] == false || $def['IsIdentity'] == true)
                {
                    $sql .= "Not ";
                }
                $sql .= "Null,";
            }
            if(isset($pKey))
            {
                $sql .= "Primary Key (" . $pKey . ")";
            }
            $sql .=
                ");";
        }
        $result = $this->query($sql);
    }

    public function connect()
    {
        // TODO: Implement connect() method.
    }

    public function query($sqlQuery)
    {
        // TODO: Implement executeQuery() method.
    }

    public function executeProcedure($proc = array())
    {
        // TODO: Implement executeProcedure() method.
    }

    public function disconnect()
    {
        return true;
    }
}