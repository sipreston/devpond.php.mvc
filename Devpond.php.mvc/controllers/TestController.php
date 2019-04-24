<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 12/08/2018
 * Time: 08:59
 */

namespace controllers;


use Model\User;
use Vision\VisionFramework\Controller;
use Vision\VisionModel\ModelFactory;

class TestController extends Controller
{
    public function indexAction()
    {

    }

    public function testdbAction()
    {
        $sub = new \Model\Subject();
        $sub->Firstname = 'Hank';
        $sub->Lastname = 'Jones';
        $sub->DateOfBirth = new \DateTime('1997-06-06');

        $user = new User();
        $user->Id = 1003;
        $sub->AddedBy = $user;


        $sub->save();
    }

    public function getuserAction()
    {
        $user = ModelFactory::get('User', 1);
        $t=0;
    }
}