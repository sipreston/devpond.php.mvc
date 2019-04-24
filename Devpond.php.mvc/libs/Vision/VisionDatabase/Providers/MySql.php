<?php

namespace Vision\VisionDatabase\Providers;

use DateTime;
use Vision\VisionDatabase\DbColumnDefinition;
use Vision\VisionDatabase\DbDataDefinition;
use Vision\VisionDatabase\DbRelatedTable;
use Vision\VisionDatabase\Interfaces\IDatabase;
use Vision\VisionDatabase\Results\DbRow;
use Vision\VisionDatabase\Results\DbResult;
use Vision\VisionDatabase\Results\DbResultSet;

Class MySql implements IDatabase
{
    protected $connection;
    protected $connected;
    protected $connDetails;
    protected $dbName;
    protected $dbType = 'STANDARD';
    protected $dbHost;

    const CONNECT_RETRIES = 5;

    const IDENTIFIER = 'Id';

    const DATE_FORMAT = 'Y-m-d h:i:s';

    public function __construct(DbProviderParameters $params = null)
    {
        if(!empty($params->connParams))
        {
            $this->setConnection($params);
        }
    }
    public function setConnection(DbProviderParameters $params)
    {
//        $db['db_host'] = $this->dbHost = $params['dbHost'];
//        $db['db_user'] = $params['dbUid'];
//        $db['db_pass'] = $params['dbPassword'];
//        $db['db_name'] = $this->dbName = $params['dbName'];

        $this->connDetails = $params->connParams;
    }
    public function connect()
    {
        for ($connectAttempts = 0; $connectAttempts < self::CONNECT_RETRIES; $connectAttempts++)
        {
            $this->connection = mysqli_connect($this->connDetails->host, $this->connDetails->user, $this->connDetails->password, $this->connDetails->name);
            if($this->connection)
            {
                $this->dbName  = $this->connDetails->name;
                break;
            }
        }
        if(!$this->connection)
        {
            die("Error connecting to the database: " . mysqli_connect_error());
        }
        else
        {
            $this->connected = true;
        }
    }

    public function disconnect()
    {
        if(@mysqli_ping($this->connection))
        {
            @mysqli_close($this->connection);
            $this->connected = false;
        }
    }

    /**
     * @param string $sqlQuery
     * @param bool $isSingle
     * @return DbResultSet
     */
    public function query($sqlQuery, $isSingle = false)
    {
        //TODO: Determine if a query is single or multi inside this method.
        if(!$this->connected)
        {
            $this->connect();
        }
        $results = Array();
        $escapedQuery = mysqli_real_escape_string($this->connection, $sqlQuery);
        if($isSingle == true)
        {
            $results = $this->executeSingleQuery($sqlQuery);
        }
        else
        {
            $results = $this->executeMultiQuery($sqlQuery);
        }

        $error = mysqli_error($this->connection);
        if(!empty($error))
        {
            $results['errors'] = $error;
        }
        $dbResultSet = new DbResultSet();
        if(isset($results['QuerySuccess']))
        {
            // store in DbResultSet
        }
        for($i = 0; $i < count($results); $i++)
        {
            $result = $results[$i];
            $dbResult = new DbResult();
            for($j = 0; $j < count($result); $j++)
            {
                $row = $result[$j];
                $dbRow = new DbRow();
                $dbRow->set($row);
                $dbResult->addRow($dbRow);
            }
            $dbResult->setOrder($i + 1);
            $dbResult->setRowCount($j);
            $dbResultSet->addResult($dbResult);

        }
        if(!empty($error))
        {
            $dbResultSet->addError($error);
        }

        return $dbResultSet;
    }

    /**
     * @param string $tableName
     * @param DbDataDefinition[] $tableData
     * @param int $id
     */
    public function getRow($tableName, $tableData, $id)
    {
        $this->Id = (int)$id;
        if(count($tableData) > 0)
        {
            $sql = "
            Select
                ";
            foreach ($tableData as $data)
            {
                if($data->property == self::IDENTIFIER)
                {
                    $primaryKeyName = $data->column;
                }
                $sql .= $data->column;
                if ($data !== end($tableData))
                {
                    $sql .= ",";
                }
            }
            $sql .= "
                From "
                . (string)$tableName;
            $sql .= " Where " . $primaryKeyName . " = " . (int)$id;
            $result = $this->query($sql);
            return $result;
        }
        return false;
    }

    /**
     * @param $tableName
     * @param DbDataDefinition[] $tableData
     * @param null $id
     */
    public function saveRow($tableName, $tableData, $id = null)
    {
        if(!isset($id))
        {
            // create new record
            $sql = "
            Insert Into "
                . (string)$tableName . "
            (";
            $isFirstPass = true;
            foreach ($tableData as $data)
            {
                if (!$isFirstPass === true) {
                    $sql .= ",";
                }
                $sql .= $data->column;
                $isFirstPass = false;
            }
            $sql .= ")
            Values
            (";
            $isFirstPass = true;
            foreach ($tableData as $data)
            {
                $val = $data->value;
                if (!$isFirstPass) {
                    $sql .= ",";
                }
                if ($data->isForeignKey == true && isset($val)) {
                    $sql .= $val;
                } else {
                    $tmpType = ltrim($data->dataType, '\\');
                    switch (strtolower($tmpType)) {
                        case 'string';
                            $sql .= isset($val) ? "'" . $val . "'" : 'null';
                            break;
                        case 'int';
                            $sql .= isset($val) ? $val : 'null';
                            break;
                        case 'float':
                        case 'decimal':
                            $sql .= isset($val) ? $val : 'null';
                            break;
                        case 'bool':
                            $sql .= $val == true ? 1 : 0;
                            break;
                        case 'date':
                        case 'datetime':
                            if ($data->property == 'CreatedDate') {
                                $sql .= 'Now()';
                            } elseif ($val instanceof DateTime) {
                                $sql .= isset($val) ? $val->format(self::DATE_FORMAT) : 'null';
                            } else {
                                $sql .= 'null';
                            }
                            break;
                        default:
                            $sql .= 'null';
                    }
                }
                $isFirstPass = false;
            }

            $sql .= ");
                Set @Id = Last_Insert_Id();
                Select @Id as Id";
        }
        else
        {
            //update
        }

        $result = $this->query($sql);
        if($result[0][0]['Id'])
        {
            return (int)$result[0][0]['Id'];
        }
    }

    public function executeProcedure($proc = array())
    {
        // TODO: Implement executeProcedure() method.
    }

    protected function fillResults($row)
    {
        $results = array();
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                $results[$key] = $value;
            }
        }
        return $results;
    }

    public function getColumns($table)
    {
        $sql = "
            Show Columns
            From " . $table
        ;

        $results = $this->query($sql);
        $cols = array();
        foreach($results[0] as $result)
        {
            $col = new DbColumnDefinition();
            $col->columnName = $result['Field'];
            $col->type = $col->getType($result['Type']);
            $col->length = $col->getLength($result['Type']);
            $col->mappedType = $col->mapType($result['Type']);
            $col->isIdentity = ($result['Key'] == 'PRI') ? true : false;
            $col->isPrimaryKey = ($result['Key'] == 'PRI') ? true : false;
            $col->autoIncrement = (strpos($result['Extra'], 'auto_increment') !== false ) ? true : false;
            $col->isNullable = ($result['Null'] == 'YES') ? true : false;
            $col->isForeignKey = ($result['Key'] == 'MUL') ? true : false;
            $col->isUnique = ($result['Key'] == 'MUL' || $col->isPrimaryKey == true) ? true : false;

            $cols[$col->columnName] = $col;
        }
        return $cols;
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

    public function saveRelatedTable(DbRelatedTable $relTable)
    {
        $keys = $relTable->getForeignKeys();
        $sql = "
                Create Table " . $relTable->getTableName() . "
                (
                ";
                foreach($keys as $part)
                {
                    $sql .= (string)$part . self::IDENTIFIER . " Int Not Null,";
                }
                foreach($keys as $part)
                {
                    $sql .= "
                        Foreign Key (" . $part . self::IDENTIFIER . ") References " . $part . "s" . "(" . $part . self::IDENTIFIER . ")";
                    if($part != end($keys))
                    {
                        $sql .= ',';
                    }
                }
                $sql .= "
                );";
        $result = $this->query($sql);
    }

    protected function getDataTypeFromVariable($variable, $size = null)
    {
        switch(strtolower($variable))
        {
            case 'int':
                return 'Int';
            case 'string':
                return 'VarChar(255)';
            case 'string(max)':
                return 'Text';
            case 'bool':
                return 'Bit';
            case 'datetime':
                return 'DateTime';
            case 'date':
                return 'Date';
            default:
                throw (new \Exception("Datatype not recognised"));
        }
    }

    public function setConn(DbProviderParameters $params)
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

    public function CheckSql($string)
    {
        // replace with SqlCheckInjection later
        global $connection;
        return mysqli_real_escape_string($connection, trim($string));
    }



    public function SqlCheckInjection($string)
    {
        global $connection;
        return mysqli_real_escape_string($connection, trim($string));
    }

    public function SqlExecuteQuery($query)
    {
        $connection = $GLOBALS['connection'];
        return mysqli_query($connection, $query);
    }

    public function SqlGetRecord($data)
    {
        return mysqli_fetch_assoc($data);
    }

    public function SqlGetRecordArray($data)
    {
        return mysqli_fetch_array($data);
    }

    public function SqlGetRowCount($data)
    {
        return mysqli_num_rows($data);
    }

    public function SqlGetErrorMsg()
    {
        $connection = $GLOBALS['connection'];
        return mysqli_error($connection);
    }

    private function executeSingleQuery($sqlQuery)
    {
        $results = Array();
        if ($result = mysqli_query($this->connection, $sqlQuery))
        {
            if(!is_bool($result))
            {
                $data = array();
                $i = 0;
                while ($row = mysqli_fetch_array($result)) {
                    $data[] = $this->fillResults($row);
                }
                if (count($data) > 0) {
                    $results[$i] = $data;
                    unset($data);
                    $i++;
                }
            }
            else
            {
                $results['QuerySuccess'] = $result;
            }
        }
        else
        {
            //TODO: Log these somewhere
        }

        return $results;
    }

    private function executeMultiQuery($sqlQuery)
    {
        $results = array();
        if(mysqli_multi_query($this->connection, $sqlQuery))
        {
            $data = array();
            $i = 0;
            do
            {
                if($result = mysqli_store_result($this->connection))
                {
                    while ($row = mysqli_fetch_array($result))
                    {
                        $data[] = $this->fillResults($row);
                    }
                    if (count($data) > 0)
                    {
                        $results[$i] = $data;
                        unset($data);
                        $i++;
                    }
                    mysqli_free_result($result);
                }

            }
            while (mysqli_more_results($this->connection) && mysqli_next_result($this->connection));
        }
        else
        {
            //TODO: Log these somewhere
        }
        return $results;
    }

//    public function __destruct()
//    {
//        $this->disconnect();
//    }

    public function __get($value)
    {
        return $this->{$value};
    }

    public function isConnectionSet()
    {
        // another rudimentary check
        if(count($this->connDetails) > 0)
        {
            return true;
        }
        return false;
    }
}