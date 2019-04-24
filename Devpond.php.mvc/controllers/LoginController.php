<?php

namespace controllers;

use Vision\VisionFramework\Controller;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->hideSideBar = true;
        parent::__construct();
    }
    public function indexAction($id = null)
    {
        $viewFields['hideSideBar'] = $this->hideSideBar;
        $this->renderView('index.phtml', $viewFields);
    }
    public function post_indexAction()
    {
        if(isset($_POST['username']) && isset($_POST['password']))
        {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $user = new User(null, $username, $password, true);
            if($user->isLoggedIn())
            {
                $T = true;
            }
        }
    }
    public function loginUserAction()
    {
        $username = strtolower($_POST['username']);
        $password = $_POST['password'];

        $username = CheckSql($username);
        $password = CheckSql($password);

        $query = "SELECT * FROM users WHERE username = '{$username}' ";
        //$query .= "AND user_password = '{$password}'";
        $select_user_query = ExecuteQuery($query);

        $row = SqlGetRecordArray($select_user_query);

        $user_id = $row['user_id'];
        $user_username = $row['username'];
        $user_password = $row['user_password'];
        $user_user_firstname = $row['user_firstname'];
        $user_user_lastname = $row['user_lastname'];
        $user_user_role = $row['user_role'];

        if (password_verify($password, $user_password))
        {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $user_username;
            $_SESSION['firstname'] = $user_user_firstname;
            $_SESSION['lastname'] = $user_user_lastname;
            $_SESSION['user_role'] = $user_user_role;
            $_SESSION['is_logged_in'] = true;

            //header("Location: /Admin/Index");
            $this->redirectToAction("Admin", "indexAction");
        } else {
            $this->redirectToAction("Index", "indexAction");
        }
    }
    public function registerAction()
    {
        $thing = "";
    }
    public function addUserAction($id)
    {
        echo "Hello from Contact action. Id passed was " . $id;
    }
    public function logoutAction()
    {
        $_SESSION['user_id'] = null;
        $_SESSION['is_logged_in'] = null;
        $_SESSION['username'] = null;
        $_SESSION['firstname'] = null;
        $_SESSION['lastname'] = null;
        $_SESSION['user_role'] = null;

        session_destroy();
        $this->redirectToAction("Index", "indexAction");
    }

    public function UsersOnlineAction()
    {
        if(SessionUserIsAdmin())
        {
            $uoc_arr = array(UsersOnlineCount());
            header('Content-type: application/json');
            echo json_encode($uoc_arr);
        }
    }
}