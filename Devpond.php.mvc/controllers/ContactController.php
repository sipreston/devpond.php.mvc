<?php

namespace controllers;

use Vision\VisionFramework\Controller;

class ContactController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->view->showSideBar = false;
        
    }

    public function indexAction()
    {
        $this->renderView('views/contact/index.phtml');
    }

    public function contactAction()
    {
        $subject = $_POST['subject'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        // do stuff here.

        $this->view->name = $name;

        $this->renderView('views/contact/success.phtml');
    }
}
