<?php

namespace controllers;

use Vision\VisionFramework\Controller;

class RegisterController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->view->showSideBar = false;
    }

    public function indexAction()
    {
        $this->renderView('views/register/index.phtml');
    }

    public function contactAction()
    {
        $this->view->message = "Hello Contacts! :) <br />";
        $this->renderView('index.phtml');
    }
}