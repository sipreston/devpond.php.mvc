<?php

namespace Vision\VisionDatabase\Results;

/**
 * Class DbRow
 * @package Vision\VisionDatabase\Results
 */
class DbRow
{
    /**
     * Row data returned by the PHP library for the DBMS
     * @var mixed[]
     */
    private $rawRowData;

    public function __construct()
    {

    }

    /**
     * Get the raw data.
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $name = (string)$name;
        if(isset($this->rawRowData)) {
            if (array_key_exists($name, $this->rawRowData)) {
                return $this->rawRowData[$name];
            }
        }
        return null;
    }

    /**
     * Set the row raw data
     * @param $rowData
     */
    public function set($rowData)
    {
        $this->rawRowData = $rowData;
    }
}
