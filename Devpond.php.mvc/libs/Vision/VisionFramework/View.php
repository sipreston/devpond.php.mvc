<?php

namespace Vision\VisionFramework;

class View
{
	// public $message;
	// public $actionName;
	// public $viewsFolder;
    public $bodyView;
    public $viewFields = array();
    public $controllerMethods = array();
    public $parentController;

	public function __construct(Controller $parentController = null)
	{
	    if(isset($parentController)) {
            $this->parentController = $parentController;
        }
        $this->viewsFolder = 'views';
	}

	public function render($viewScript, $viewFields = array())
	{
        if(isset($viewFields)) {
            $this->mergeViewFields($viewFields);
        }
        ob_start();
        if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $viewScript)) {
            include($_SERVER["DOCUMENT_ROOT"] . '/' . $viewScript);
        }
        $viewContent = ob_get_clean();

        echo $viewContent;
	}
	public function renderBody($viewScript)
	{
		include($viewScript);

	}

    public function renderSimple($viewScript, $viewFields = array())
    {
        //This will do for now.
        $html = '<html><head></head><body>';
        if(isset($viewFields)) {
            $this->mergeViewFields($viewFields);
        }
        ob_start();
        if(file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $viewScript)) {
            include($_SERVER["DOCUMENT_ROOT"] . '/' . $viewScript);
        }
        $html .= ob_get_clean();
        $html .= '</body></html>';

        echo $html;
    }

	public function renderResolver($resolver)
    {

        $resolver = str_replace("Resolver", "", $resolver) . 'Resolver';
        $resolver = 'Resolvers\\' . $resolver;
        /**
         * @var ViewResolver
         */
        $viewResolver = new $resolver;
        $viewData = $viewResolver->getViewData();
        if(isset($viewData['viewFields'])) {
            $this->mergeViewFields($viewData['viewFields']);
        }
        $returnScript = $viewData['view'];
        $this->render($returnScript);
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

    public function __call($methodName, $args)
    {
        if(method_exists($this, $methodName))
        {
            call_user_func_array(array($this, $methodName), $args);
        }
        else if(in_array($methodName, $this->controllerMethods))
        {
            call_user_func_array(array($this->parentController, $methodName), $args);
        }
    }

    public function methodExists($methodName)
    {
        if(in_array($methodName, $this->controllerMethods))
        {
            return true;
        }
        return false;
    }
}