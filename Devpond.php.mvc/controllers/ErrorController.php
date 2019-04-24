<?php

namespace controllers;

use Vision\VisionFramework\Controller;

class ErrorController extends Controller
{
	public function __construct(){
		parent::__construct();
	}
	public function indexAction($id = null)
	{
		$this->view->message = "The controller doesn't exist!";
		$this->renderView('views/error/index.phtml');
	}
}