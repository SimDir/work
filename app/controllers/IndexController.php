<?php

namespace rrdev\controllers;

use rrdev\core\Controller;

class indexController extends Controller {

    public function indexAction($param = null) {

        return $this->view->render('home.html');
    }

}
