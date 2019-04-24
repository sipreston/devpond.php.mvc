<?php

namespace Vision\VisionDatabase\Results;

/**
 * Class DbResultSet
 * @package Vision\VisionDatabase\Results
 */
class DbResultSet
{
    /**
     * @var DbResult[]
     */
    private $dbResults  = [];

    /**
     * @var string[]
     */
    private $errors     = [];

    public function __construct()
    {

    }

    /**
     * Add a result of rows to the set
     * @param DbResult $dbResult
     */
    public function addResult(DbResult $dbResult)
    {
        if($dbResult->isValid()) {
            $this->dbResults[$dbResult->getOrder()] = $dbResult;
        }
    }

    /**
     * Add any errors occurring during the transaction
     * @param $error
     */
    public function addError($error)
    {
        $this->errors[] = (string)$error;
    }

    /**
     * Get number of results in the set
     * @return int
     */
    public function getResultCount()
    {
        return count($this->dbResults);
    }

    /**
     * Get the nth result in the set
     * @param int $setNumber
     */
    public function get($setNumber = 1)
    {
        return $this->dbResults[$setNumber];
    }
}
