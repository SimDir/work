<?php

namespace rrdev\controllers;

use rrdev\core\Controller;

defined('ROOT') OR die('No direct script access.');

class IndexController extends Controller {

    public function IndexAction($param = null) {

        return $this->View->Render('home.html');
    }

}
