<?php

namespace resolvers;

use Vision\VisionFramework\ViewResolver;

class LoginPanelResolver extends ViewResolver
{
    public $viewFields;

    public function __construct()
    {
        parent::__construct();
    }

    public function getViewData()
    {
        return array('view' => 'views/partials/login_panel.phtml');
    }

}