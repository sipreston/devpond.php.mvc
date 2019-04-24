<?php

namespace Resolvers;

use Model\User;
use Vision\VisionDatabase\DbHandler;
use Vision\VisionFramework\ViewResolver;

class NavResolver extends ViewResolver
{
    public $db;
    public $user;
    public $viewFields;

    public function __construct()
    {
        $this->db = (new DbHandler())->getDbProvider();
        $this->user = new User();
        parent::__construct();
    }

    public function getViewData()
    {
        $viewFields = array();
        $viewFields['menuLinks'] = $this->getMenuLinks();
        //$viewFields['isLoggedIn'] = $this->user->isLoggedIn();
        //$viewFields['isLoggedIn'] = true;
        //$viewFields['isAdmin'] = $this->user->userIsAdmin();

        return array('view' => 'views/partials/navigation.phtml', 'viewFields' => $viewFields);
    }

    public function getMenuLinks()
    {
        //TODO: Not return an empty array
        $menuLinks = array();
        return $menuLinks;
    }
}