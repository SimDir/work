<?php

namespace rrdev\controllers;

use rrdev\core\Controller;
use rrdev\core\session\SessionManager;
use \rrdev\core;

defined('ROOT') OR die('No direct script access.');

class AdminController extends Controller {

    public function __construct() {
        parent::__construct();
        $LoggedUser = SessionManager::get('LoggedUser');
//        dd($LoggedUser);
        if (is_null($LoggedUser)or($LoggedUser['role'] < 900)) {
            SessionManager::set('redirectfrom', filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));
            core::Redirect('/user/login');
            die('access denied');
        }
    }

    public function IndexAction($param = null) {
        $this->View->DashboardContent = $this->View->Render('main.html');
        return $this->View->Render('dashboard.html');
    }

//    public function DemoAction() {
//        $faker = \Faker\Factory::create('ru_RU');
//        $faker->seed(1234);
//        $um= new UserModel();
//        for ($i = 1; $i <= 500; $i++) {
//
//
//        $name = explode(' ',$faker->name);
//        $param['firstname']=$name[0];//$faker->firstName;
//        $param['patronymic']=$name[1];
//        $param['lastname']=$name[2];
//        
//        $param['login']=$faker->userName;
//        $param['email']=$faker->email;
//        $param['phone']=$faker->phoneNumber;
//        $param['birthday']=$faker->dateTimeThisCentury->format('Y-m-d');
//        $param['address']=$faker->address;
//        $param['password']=$faker->password;
//        
//        $um->Action_Registre($param);
//        $name =[];
//        }
//        $this->View->DashboardContent = $faker->name;
//        return $param;//$this->View->Render('dashboard.html');
//    }
}
