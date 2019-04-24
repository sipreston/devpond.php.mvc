<?php

namespace Vision\VisionFramework;

use Vision\VisionDatabase\DbHandler;
use Vision\VisionModel\ModelFactory;

abstract class Controller
{
    protected $user;
    protected $db;
    protected $dbHandler;
    protected $controllerName;

    public $viewFields = array();
    public $siteScripts;
    public $siteStyles;
    public $urlFromPrefix = false;

	public function __construct()
	{
        $this->getParameters();
        $this->controllerName = $this->getControllerName();
        $this->controllerMethods = $this->getControllerMethods();
        $this->db = (new DbHandler())->getDbProvider();
        //$this->db = $this->dbHandler->getDbProvider();
	    $this->user = ModelFactory::get('User');
		$this->view = new View($this);
        if(!$this->siteScripts instanceof SiteScripts) {
            $this->siteScripts = new SiteScripts();
        }
        if(!$this->siteStyles instanceof SiteStyles) {
            $this->siteStyles = new SiteStyles();
        }
        $this->addDefaultScripts();
        $this->addDefaultStyles();

		$this->addPageProperties();
	}

	abstract function indexAction();

	protected function addPageProperties()
	{
	    $viewFields['pageName'] = $this->getControllerName();
        $this->mergeViewFields($viewFields);
	}

	protected function redirectToAction($controller, $actionName = 'index')
	{
		$controllerName = $controller . "Controller";
        $actionName = strtolower($actionName) . 'Action';
        $controller = new $controllerName;
        $controller->{$actionName}();
	}
	protected function renderView($viewscript = null, $viewFields = array())
	{
	    $viewParts = explode('/', $viewscript);
        if(count($viewParts) == 1)
        {
            $viewPath = 'views/' . strtolower($this->controllerName) . '/' . $viewscript;
        }
        else
        {
            $viewPath = '';
            foreach($viewParts as $part)
            {
                if(stripos(strtolower($part), 'phtml'))
                {
                    $viewPath .= $part;
                }
                else {
                    $viewPath .= $part . '/';
                }
            }
        }
//	    if($viewscript != preg_match('#^/#*', $viewscript)) {
//            $viewscript = 'views/' . strtolower($this->controllerName) . '/' . $viewPath;
//        }
	    $this->mergeViewFields($viewFields);
		$this->view->bodyView = $viewPath;
        $this->view->controllerMethods = $this->controllerMethods;
		ob_start();
		include($viewPath);
		// need to pull in the viewscript page details and get its layout property.
		if(!empty($this->layoutPage)) {
			$layoutPage = $this->layoutPage;
		} else {
			$layoutPage = "views/shared/layout.phtml"; // default layout
		}
		ob_clean();
		$this->view->render($layoutPage, $this->viewFields);
	}

    protected function getControllerMethods($returnAll = false)
    {
        $publicMethods = array();
        $methods = get_class_methods($this);
        if (!$returnAll) {
            foreach ($methods as $method) {
                $reflect = new \ReflectionMethod($this, $method);
                if ($reflect->isPublic()) {
                    $publicMethods[] = $method;
                }
            }
        }
        return $publicMethods;
    }

    protected function getParameters()
    {
        // may need better validation on this really.
        $this->requestParams = $_REQUEST;
    }

    protected function renderSimple($viewScript, $viewFields = array())
    {
        $viewParts = explode('/', $viewScript);
        if(count($viewParts) == 1)
        {
            $viewPath = 'views/' . strtolower($this->controllerName) . '/partials/' . $viewScript;
        }
        else
        {
            $viewPath = '';
            foreach($viewParts as $part)
            {
                if(stripos(strtolower($part), 'phtml'))
                {
                    $viewPath .= $part;
                }
                else {
                    $viewPath .= $part . '/';
                }
            }
        }
        $this->mergeViewFields($viewFields);

        $this->view->renderSimple($viewPath, $this->viewFields);
    }

    public function renderScripts($loadMethod = 'PageLoad')
    {
        $this->siteScripts->Sort();
        switch($loadMethod)
        {
            case 'AfterPageLoad';
                $availableScripts = $this->siteScripts->afterPageLoadScripts;
                break;
            default:
            case 'PageLoad';
                $availableScripts = $this->siteScripts->onPageLoadScripts;
                break;

        }
        $html = '';
        foreach($availableScripts as $script)
        {
            $html .= '<script src="' . $script->location . '/'
                . $script->fileName . '"';
            if(isset($script->type))
            {
                $html .= ' type="' . $script->type . '" ';
            }
            $html .= '></script>';
        }
        echo $html;
    }

    public function renderStyles()
    {
        $availableStyles = $this->siteStyles->availableStyles;
        $html = '';
        foreach($availableStyles as $style)
        {
            $html .= '<link href="' . $style->location . '/'
                . $style->fileName . '" rel="stylesheet">';
        }
        echo $html;
    }

    protected function addDefaultScripts()
    {
        $this->siteScripts->Add(new Script(
            'jquery.js',
            '/scripts',
            true
        ));
        $this->siteScripts->Add(new Script(
            'bootstrap.min.js',
            '/scripts',
            false,
            null,
            false,
            true
        ));
    }

    protected function addDefaultStyles()
    {
        $this->siteStyles->Add(new Style(
            'bootstrap.min.css',
            '/css'
        ));
        $this->siteStyles->Add(new Style(
            'blog-home.css',
            '/css'
        ));
    }


    private function mergeViewFields($fields)
    {
        $this->viewFields = array_merge($this->viewFields, $fields);
    }

    public function __get($name)
    {
        if(isset($this->viewFields[$name])) {
            return $this->viewFields[$name];
        }
        return null;
    }

    public function urlLink($controller, $actionName = 'index', $linkText = '')
    {
        //TODO: check icoming request is with controller prefix or not. As the former breaks.
        //Ideally this should be in another library, as this deals with html syntax and styling.
        $isControllerPrefix = false;
        $host = $_SERVER['HTTP_HOST'];
        $hostParts = explode('.', $host);
        $controllerFileName  = ucfirst($hostParts[0]);
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/controllers/' . $controllerFileName . 'Controller.php'))
        {
            $isControllerPrefix = true;
        }
        $controller = strtolower($controller);
        $actionName = strtolower($actionName);
        $port = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
        if($isControllerPrefix)
        {
            $html = '<a href="' . $port . $host . '/' .  $actionName . '">' . $linkText . '</a>';
        }
        else
        {
            $html = '<a href="' . $port . $host . '/' . $controller . '/' . $actionName . '">' . $linkText . '</a>';
        }


        echo $html;
    }

    protected function getControllerName()
    {
        $ref = new \ReflectionClass($this);
        $className = $ref->getShortName();
        $className = str_replace('Controller', '', $className);
        return $className;
    }
}