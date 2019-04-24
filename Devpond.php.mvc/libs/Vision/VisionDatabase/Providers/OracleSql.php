<?php

namespace Vision\VisionDatabase\Providers;

use Vision\VisionDatabase\Interfaces\IDatabase;

class OracleSql implements IDatabase
{
    public function __construct($connParams = array())
    {

    }

    public function setConnection($params = array())
    {
        // TODO: Implement setConnection() method.
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

    public function getColumns($table)
    {
        // TODO: Implement getColumns() method.
    }

    public function getTables($dbName = null)
    {
        // TODO: Implement getTables() method.
    }

    public function setConn($dbSettings)
    {
        // TODO: Implement setConn() method.
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
    }
}