<?php

namespace Vision\VisionFramework;

use Vision\VisionModel\ModelMap;

class Bootstrap
{
    /**
     * @const string
     */
    const NAMESPACE_CONTROLLERS = 'Controllers\\';

    private $controllerFromPrefix = false;
    private $controllerName;
    private $tokens;
    private $indexAction = "indexAction";

    public function __construct()
    {
        session_start();
        //ModelMap::clear();
        $this->getRequestedPage();
    }

    protected function getRequestedPage() {

        //1. Router
        $incomingUrl = strtok($_SERVER['REQUEST_URI'], '?');
        $tokens = explode('/', rtrim($incomingUrl, '/'));
        $actionName = "";
        $indexAction = "indexAction";
        if($incomingUrl == '/favicon.ico')
            return; // not implemented yet;

        //2. Dispatcher
        $controllerName = $this->getControllerByUrlPrefix();
        if($controllerName || isset($tokens[1]))
        {
            $controllerName = $controllerName ? $controllerName . "Controller" : ucfirst($tokens[1]) . "Controller";
            //$controllerName = ucfirst($tokens[1]) . "Controller";
            $controllerName = str_replace(".php", "", $controllerName);

            if(file_exists('controllers/' . $controllerName . '.php'))
            {
                $controllerName = self::NAMESPACE_CONTROLLERS . $controllerName;
                $controller = new $controllerName;
                if($this->controllerFromPrefix)
                {
                    // moved to own method
                    $controller->urlFromPrefix = true;
                    $this->routeThroughPrefix($controller, $tokens);

                }
                else if(isset($tokens[2]))
                {
                    if((int)$tokens[2])
                    {
                        $actionName = $this->checkRequestType($indexAction);
                        $id = $tokens[2];
                        $controller->{$actionName}($id);
                    }
                    else if(isset($tokens[3]))
                    {
                        $id = $tokens[3];
                        $actionName = $this->checkRequestType($actionName . $tokens[2] . "Action");
                        $controller->{$actionName}($id);
                    }
                    else
                    {
                        $actionName = $this->checkRequestType($actionName . $tokens[2] . "Action");
                        $controller->{$actionName}();
                    }
                } else {
                    //default action
                    $actionName = $this->checkRequestType($indexAction);
                    $controller->{$actionName}();
                }
            }
            else
            {
                $this->showError();
            }
        }
        else {
            $this->showIndex();
        }
    }

    protected function routeThroughPrefix($controller, $tokens)
    {
        if(isset($tokens[1]))
        {
            // have they used the controller name as well as the in the prefix?
            // what happens if we have Index/Index btw?
            if(file_exists('controllers/' . ucfirst($tokens[1]) . 'Controller.php'))
            {
                if($this->methodExists($controller, $tokens[1] . "Action"))
                {
                    $actionName = $this->checkRequestType($tokens[1] . "Action");
                }
                else {
                    $actionName = $this->checkRequestType($tokens[2] . "Action");
                }
            }
            else {
                $actionName = $this->checkRequestType($tokens[1] . "Action");
            }

        }
        else
        {
            $actionName = $this->checkRequestType($this->indexAction);
        }
        if(isset($tokens[2]))
        {
            $id = $tokens[2];
            $controller->{$actionName}($id);
        }
        else
        {
            $controller->{$actionName}();
        }
    }

    protected function showError()
    {
        $controllerName = "ErrorController";
        $actionName = "indexAction";
        $controller = new $controllerName;
        $controller->{$actionName}(null);
    }

    protected function showIndex()
    {
        $controllerName = "Controllers\IndexController";
        $actionName = "indexAction";

        $controller = new $controllerName;

        $controller->{$actionName}();
    }

    protected function getControllerByUrlPrefix()
    {
        $urlSplit = explode('.', $_SERVER['HTTP_HOST']);
        $controllerName = ucfirst($urlSplit[0]);
        $controllerFileName = $controllerName . 'Controller';
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/controllers/' . $controllerFileName . '.php'))
        {
            $this->controllerFromPrefix = true;
            return self::NAMESPACE_CONTROLLERS . $controllerName;
        }
        return false;
    }

    protected function getControllerActionMethods($controller)
    {
        $actionMethods = array();
        $methods = get_class_methods($controller);
        foreach($methods as $method)
        {
            $reflect = new \ReflectionMethod($controller, $method);
            if($reflect->isPublic())
            {
                if(preg_match("/Action/", $method))
                    $actionMethods[] = $method;
            }
        }
        return $actionMethods;
    }

    protected function methodExists($controller, $method)
    {
        $controllerMethods = $this->getControllerActionMethods($controller);
        if(in_array($method, $controllerMethods))
        {
            return true;
        }
        return false;
    }

    protected function checkRequestType($actionName)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionName = "post_" . $actionName;
        }
        else if(strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest')
        {
            $actionName = "ajax_" . $actionName;
        }
        return $actionName;
    }
}