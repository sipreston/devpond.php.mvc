<?php

namespace resolvers;

use Vision\VisionFramework\ViewResolver;

class SideBarResolver extends ViewResolver
{
    public $db;

    public $user;

    public $viewFields;

    public function __construct()
    {
        $this->db = (new \Vision\VisionDatabase\DbHandler())->getDbProvider();
        parent::__construct();
    }

    public function getViewData()
    {
        $viewFields = array();
        $viewFields['categories'] = $this->getCategories();

        return array('view' => 'views/partials/sidebar.phtml', 'viewFields' => $viewFields);
    }

    public function getCategories()
    {
        //return empty array for now.
        $categories = array();
        return $categories;
    }
}