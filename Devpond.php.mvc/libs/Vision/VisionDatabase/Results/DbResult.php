<?php

namespace Vision\VisionDatabase\Results;

use Vision\VisionDatabase\DbError;

/**
 * Class DbResult
 * @package Vision\VisionDatabase\Results
 */
class DbResult
{
    /**
     * @var int
     */
    private $executionTime;
    /**
     * @var int
     */
    private $order;
    /**
     * @var int
     */
    private $rowCount   = 0;
    /**
     * @var DbError[];
     */
    private $errors = [];
    /**
     * @var string
     */
    private $queryString;
    /**
     * @var DbRow[];
     */
    private $rows;

    public function __construct()
    {

    }

    public function isValid()
    {
        return true;
    }

    /**
     * Get all data in the result.
     * @return DbRow[]
     */
    public function getAll()
    {
        return $this->rows;
    }

    /**
     * Check if any errors occurred during retrieval
     * @return bool
     */
    public function hasErrors()
    {
        if(count($this->errors) > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * Check if the result has any data.
     * @return bool
     */
    public function hasRows()
    {
        if(count($this->rows) >0)
        {
            return true;
        }
        return false;
    }

    /**
     * @param mixed[] $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * Total rows in the result
     * @return int
     */
    public function getRowCount()
    {
        return (int)$this->rowCount;
    }

    /**
     * @param int $rowCount
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = (int)$rowCount;
    }

    /**
     * Return the query used in the retrieval attempt
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param string $queryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = (string)$queryString;
    }

    /**
     * Add a row to the results
     * @param DbRow $rowData
     */
    public function addRow(DbRow $rowData)
    {
        $this->rows[] = $rowData;
    }

    /**
     * Get SQL query execution time
     * @return int
     */
    public function getExecutionTime()
    {
        return $this->executionTime;
    }

    /**
     * Set SQL query execution time
     * @param $time
     */
    public function setExecutionTime($time)
    {
        $this->executionTime = (int)$time;
    }

    /**
     * This result's place in a result set
     * @param $order
     */
    public function setOrder($order)
    {
        $this->order = (int)$order;
    }

    /**
     * Get results place in a result set
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Add an error picked up during transaction
     * @param DbError $dbError
     */
    public function addError(DbError $dbError)
    {
        $this->errors[] = $dbError;
    }

    /**
     * Get the first n rows from the result
     * @param int $takeCount
     * @return array
     */
    public function take($takeCount = 1)
    {
        for($i = 0; $i < $takeCount; $i++)
        {
            $ret[] = $this->rows[$i];
        }
        return $ret;
    }

    /**
     * Get the top row
     * @return DbRow
     */
    public function getFirst()
    {
        return $this->rows[0];
    }
}
