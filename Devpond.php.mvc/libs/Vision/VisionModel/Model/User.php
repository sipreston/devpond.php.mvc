<?php

namespace Model;

use Vision\VisionModel\Model;

class User extends Model
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
    public $Guid;
    /**
     * @model
     * @property
     * @var string
     */
    public $Username;
    /**
     * @model
     * @property
     * @var string
     */
    public $Firstname;
    /**
     * @model
     * @property
     * @var string
     */
    public $Lastname;
    /**
     * @model
     * @property
     * @var string
     */
    public $Email;
    /**
     * @model
     * @property
     * @var \DateTime
     */
    public $DateCreated;
    /**
     * @model
     * @property
     * @var \DateTime
     */
    public $DateModified;
    /**
     * @model
     * @property
     * @var Image
     */
    public $ProfileImageId;
    /**
     * @model
     * @property
     * @var Role[]
     */
    public $Roles = array();

    private $isLoggedIn;

    private $password;

    public function __construct($id = null, $username = null, $password = null, $logon = false)
    {
        parent::__construct();
        //$this->DateCreated = new DateTime();
        if(isset($id))
        {
            $this->get($id);
            //$this->getUserById($id);
        }
        if($logon == true && isset($username) && isset($password))
        {
            $this->logon($username, $password);
        }
//        if(isset($this->Id)) {
//            $this->addToMap();
//        }
    }

    public function onConstruct()
    {

    }

    private function getCurrentUser()
    {
        return $this;
    }

    public function getUserById($id)
    {
        if(is_numeric($id))
        {
            $whereId = 'UserId = ';
        }
        else
        {
            $whereId = 'UserGuid = ';
        }
        $sql = "
            Select 
                UserId,
                UserGuid,
                Username,
                Firstname,
                Lastname,
                Email,
                ProfileImageId,
                DateCreated
                
            From Users U
              Where " . $whereId . "'" . $id . "'
                
            Select
                R.RoleId,
                R.RoleName
            From
                UserRoles UR
            Join Roles R
                On UR.RoleId = R.RoleId
            Join Users U
                On U.UserId = UR.UserId
            Where U.UserId =
                (
                    Select UserId
                    From Users
                    Where " . $whereId . "'" . $id . "' 
                )
        ";

        $result = $this->db->query($sql);
        if($result[0][0])
        {
            $user = $result[0][0];
            $this->Id = $user['UserId'];
            $this->Guid = $user['UserGuid'];
            $this->Username = $user['Username'];
            $this->Firstname = $user['Firstname'];
            $this->Lastname = $user['Lastname'];
            $this->Email = $user['Email'];
            $this->DateCreated = $user['DateCreated'];
            $this->ProfileImageId = $user['ProfileImageId'];

            if($result[1])
            {
                $roles = array();
                $roleResults = $result[1];
                foreach($roleResults as $role)
                {
                    $roles[$role['RoleName']] = $role;
                }
                $this->Roles = $roles;
            }
        }
    }

    public function logon($username = null, $password = null)
    {
        $sql = "
            Select 
                UserId,
                UserGuid,
                Username,
                Firstname,
                Lastname,
                Email,
                ProfileImageId,
                DateCreated
                
            From Users U
                Where Username = '" . $username . "' 
                And Password = '" . $password . "'
                And Active = 1
                
            Select
                R.RoleId,
                R.RoleName
            From
                UserRoles UR
            Join Roles R
                On UR.RoleId = R.RoleId
            Join Users U
                On U.UserId = UR.UserId
            Where U.UserId =
                (
                    Select UserId
                    From Users
                    Where Username = '" . $username . "' and Password = '" . $password . "'
                )
        ";

        $result = $this->db->query($sql);
        if($result)
        {
            $user = $result[0][0];
            $this->Id = $user['UserId'];
            $this->Guid = $user['UserGuid'];
            $this->Username = $user['Username'];
            $this->Firstname = $user['Firstname'];
            $this->Lastname = $user['Lastname'];
            $this->Email = $user['Email'];
            $this->ProfileImageId = $user['ProfileImageId'];
            $this->DateCreated = $user['DateCreated'];

            if($result[1])
            {
                $roles = array();
                $roleResults = $result[1];
                foreach($roleResults as $role)
                {
                    $roles[] = $role;
                }
                $this->Roles = $roles;
            }
            $this->isLoggedIn = true;

            //move this sql to a Session model later
            $sessionid = session_id();
            $sessionSql = "
                Insert Into
                  UserSessions
                  (
                    SessionId,
                    UserGuid,
                    LogonTime
                  )
                  Values
                  (
                    '" . $sessionid . "',
                    '" . $this->Guid . "',
                    " . strtotime("now") . "
                  )
            ";
            $this->db->query($sessionSql);
            $cookieName = 'session';
            setcookie($cookieName, $sessionid, time() + (86400 * 30), "/"); // 86400 = 1 day
            $_SESSION['User'] = $this;
        }
    }

    public function isAdmin()
    {
       if(in_array('Admin', $this->Roles))
       {
           return true;
       }
       return false;
    }
    public function isLoggedIn()
    {
       return $this->isLoggedIn;
    }
	
	public function getFirstName(){
		return $this->Firstname;
	}
	public function setFirstName($value){
		$this->Firstname = $value;
	}
	public function getLastName(){
		return $this->Lastname;
	}
	public function setLastName($value){
		$this->Lastname = $value;
	}
	public function getUsername(){
		return $this->Username;
	}
	public function setUsername($value){
		$this->Username = $value;
	}
	public function getEmail(){
		return $this->Email;
	}
	public function setEmail($value){
		$this->Email = $value;
	}

	public function setPassword($password)
    {
        $this->password = (string)$password;
    }

    public function getPassword()
    {
        return $this->password;
    }
}