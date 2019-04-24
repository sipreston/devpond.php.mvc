<?php

namespace Vision\VisionDatabase\Interfaces;

use Vision\VisionDatabase\DbDataDefinition;
use Vision\VisionDatabase\DbRelatedTable;
use Vision\VisionDatabase\Providers\DbProviderParameters;
use Vision\VisionDatabase\Results\DbResultSet;

interface IDatabase
{
    /**
     * Set the connection settings to the required DBMS.
     * @param DbProviderParameters $params
     * @return mixed
     */
    public function setConnection(DbProviderParameters $params);

    /**
     * Connect to the DBMS
     * @return mixed
     */
    public function connect();

    /**
     * Disconnect from the DBMS
     * @return mixed
     */
    public function disconnect();

    /**
     * @param string $sqlQuery
     * @return DbResultSet
     */
    public function query($sqlQuery);

    /**
     * Execute a stored procedure
     * @param array $proc
     * @return mixed
     */
    public function executeProcedure($proc = array());

    /**
     * Get column data from a specified table
     * @param $table
     * @return mixed
     */
    public function getColumns($table);

    /**
     * Get table data from a specified database
     * @param null $dbName
     * @return mixed
     */
    public function getTables($dbName = null);

    /**
     * Check is there is an active connection the database
     * @return mixed
     */
    public function isConnectionSet();

    /**
     * Save table data
     * @param $tableName
     * @param $tableDefinition
     * @return mixed
     */
    public function saveTable($tableName, $tableDefinition);

    /**
     * Save a table designed to hold 1-1 relationship data.
     * @param $relatedTable
     * @return mixed
     */
    public function saveRelatedTable(DbRelatedTable $relatedTable);
    //public function setConn(DbProviderParameters $params);

    /**
     * Save row data to a table.
     * @param      $tableName
     * @param      $tableData
     * @param null $id
     * @return mixed
     */
    public function saveRow($tableName, $tableData, $id = null);

    /**
     * @param string $tableName
     * @param DbDataDefinition[] $tableData
     * @param int $id
     * @return DbResultSet
     */
    public function getRow($tableName, $tableData, $id);
}