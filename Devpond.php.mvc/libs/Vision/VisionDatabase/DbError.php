<?php

namespace Vision\VisionDatabase;

/**
 * Class DbError
 * @package Vision\VisionDatabase
 *
 * @property $queryString
 */
class DbError
{
    /**
     * @var string
     */
    private $queryString;

    /**
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
}