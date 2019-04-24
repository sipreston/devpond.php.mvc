<?php

namespace Model;

use Vision\VisionModel\Model;

class UserSession extends Model
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
     * @var int
     */
    public $LogonTime;
    /**
     * @model
     * @property
     * @var string
     */
    public $SessionData;
    /**
     * @model
     * @property
     * @var string
     */
    public $SessionId;
}