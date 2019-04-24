<?php

namespace Vision\VisionDatabase;

/**
 * Class DbConnection
 * @package Vision\VisionDatabase
 *
 * @property $name
 * @property $user
 * @property $host
 * @property $password
 * @property $type
 * @property $fileName
 * @property $logFileName
 * @property $isDefault
 */
class DbConnection
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $user;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $fileName;
    /**
     * @var string
     */
    private $logFileName;
    /**
     * @var bool
     */
    private $isDefault = false;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        if($name == 'fileName')
        {
            $this->setFileNames($value);
        }
        else
        {
            switch($name)
            {
                case 'name':
                case 'host':
                case 'user':
                case 'type':
                case 'password':
                case 'isDefault':
                {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * @param $fileName
     */
    private function setFileNames($fileName)
    {
        $baseFileName = $fileName;
        if(strpos($fileName, '.mdf'))
        {
           $baseFileName = str_replace('.mdf', '', $baseFileName);
        }
        $this->fileName = $baseFileName . '.mdf';
        $this->logFileName = $baseFileName . '_log.ldf';

    }
}