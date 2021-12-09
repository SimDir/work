<?php

namespace rrdev\controllers;

use rrdev\core\Controller;
use rrdev\core\session\SessionManager;
use rrdev\models\user\UserModel;
use rrdev\core\Auth;
use rrdev\core;

defined('ROOT') OR die('No direct script access.');

class UserController extends Controller {

    private $user;
    private $auth;

    public function __construct() {
        parent::__construct();
        $this->user = new UserModel();
        $this->auth = new Auth();
        $this->View->redirectfrom = SessionManager::get('redirectfrom') ?: '/user/';
    }

    public function indexAction($param = null) {
        $LoggedUser = $this->auth->user();

        if (is_null($LoggedUser)) {
            return core::Redirect('/user/login');
        }
        $this->view->VarSetArray($LoggedUser);
        $this->view->userContent = $this->view->Render('usercard.html');
        return $this->view->Render('layout.html');
    }

    public function submitAction($param = null) {
        if ($this->data) {
            $user = array_merge($this->auth->user(), $this->data);
            $this->auth->saveUser($user);
        }

        return core::Redirect('/user/');
    }

    public function submitpwdAction($param = null) {
        if ($this->data) {
            if ($this->data['password1'] !== $this->data['password2']) {
                return 'Пароли не совпадают';
            }
            $setPwdRet = $this->auth->setPwd($this->data['password2']);
            if (is_array($setPwdRet)) {
                $errorText = '';
                foreach ($setPwdRet as $value) {
                    $errorText .= $value . '! ' . PHP_EOL;
                }
                return $errorText;
            }
        }
        return core::Redirect('/user/');
    }

    public function signupAction($param = null) {
        return $this->view->Render('userform.html');
    }

    public function loginAction($param = null) {
        return $this->view->Render('userform.html');
    }

    public function logoutAction($param = null) {
        SessionManager::destroyAll();
        return core::Redirect('/user/login');
    }

}
