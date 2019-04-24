<?php

namespace Model;

use Vision\VisionModel\Model;

class Role extends Model
{
    /**
     * @model
     * @property
     * @var int
     */
    public $Id;

    /**
     * @model
     * @property
     * @var string
     */
    public $RoleName;

    public function __construct($id = null)
    {
        parent::__construct();
        if(isset($id))
        {
            $this->get($id);

        }
    }

    public function onConstruct()
    {

    }
}