<?php

namespace Vision\VisionDatabase\Providers;

use Vision\VisionDatabase\DbConnection;

/**
 * Class DbProviderParameters
 * @package Vision\VisionDatabase\Providers
 *
 * @property $connParams
 * @property $providerType
 */
class DbProviderParameters
{
    /**
     * @var string
     */
    private $providerName;
    /**
     * @var string
     */
    private $providerType;
    /**
     * @var DbConnection
     * @property
     */
    private $connParams;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        if($name == 'connParams' && $value instanceof DbConnection)
        {
            $this->connParams = $value;
        }
        elseif($name == 'providerName' || $name == 'providerType')
        {
            $this->$name = $value;
        }
    }

    public function getProviderType()
    {
        if(!empty($this->providerType))
        {
            return $this->providerType;
        }
        return null;
    }
}