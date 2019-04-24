<?php

namespace Controllers;

use Vision\VisionFramework\Controller;

class IndexController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction($id = null)
	{
	    $viewFields = array();
		$selected_page = $this->setPage();
		//$page_posts = $this->getPosts($selected_page);

		//$viewFields['pagePosts'] = $page_posts;
		$viewFields['selected_page'] = $selected_page;
        $viewFields['showSideBar'] = true;

		$this->renderView('views/index/index.phtml', $viewFields);
	}

	public function setPage()
	{
		$page = "";
		if (isset($_GET['page'])) {
			$page = CheckSql($_GET['page']);
		}

		if ($page == "" || $page == 1) {
			$selected_page = 0;
		} else {
			$selected_page = ($page * 5) - 5;
		}

		return $selected_page;
	}
}