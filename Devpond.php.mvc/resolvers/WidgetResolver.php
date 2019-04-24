<?php

namespace resolvers;

use Vision\VisionFramework\ViewResolver;

class WidgetResolver extends ViewResolver
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getViewData()
    {
        return array('view' => 'views/partials/widget.phtml');
    }

}