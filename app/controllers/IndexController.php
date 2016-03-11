<?php

namespace marser\app\controllers;

use \marser\app\controllers\BaseController;

class IndexController extends BaseController{

    public function testAction(){
        echo 'test access';
    }

    public function notFoundAction(){
        echo 'not found';
    }
}