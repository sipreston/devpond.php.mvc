<?php

namespace Model;

use Vision\VisionModel\Model;

class UserRole extends Model
{
    /**
     * @model
     * @property
     * @var User
     */
    public $User;
    /**
     * @model
     * @property
     * @var Role
     */
    public $Role;

}