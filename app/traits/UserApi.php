<?php

namespace rrdev;

defined('ROOT') OR die('No direct script access.');

trait UserApi {

////    use AccessUserTokens;
//    public function __construct() {
//        parent::__destruct();
//        $this->TableName ='user';
//    }
    public function Action_Login($param = null) {
        if ($u = Session::get('LoggedUser')) {
            core::headerError();
            return ['error' => L('user is login', $u['login'])];
        }
        if (isset($param['data'])) {
            $param = $param['data'];
        }
        if (!isset($param['login'])) {
            core::headerError();
            return ['error' => L('login not set')];
        }
        if (!isset($param['password'])) {
            core::headerError();
            return ['error' => L('password not set')];
        }
        if (!$ret = $this->login($param['login'], $param['password'])) {
            core::headerError();
            return ['error' => L('login or password error')];
        }
        return ['success' => $ret,];
    }

    public function Action_Get() {
        return Session::get('LoggedUser');
    }

    public function Action_GetRole() {
        if ($ct = $this->CheckToken($param)) {
            return $ct;
        }
        if ($ct = $this->CheckUserToken($param)) {
            return $ct;
        }
        return Session::get('LoggedUser')['role'];
    }

    public function Action_Registre($param = null) {
        if (isset($param['data'])) {
            $param = $param['data'];
        }
        if (!isset($param['login'])) {
            core::headerError();
            return ['error' => L('login not set')];
        }
        if (!isset($param['email'])) {
            core::headerError();
            return ['error' => L('email not set')];
        }
        if (filter_var(filter_var($param['email'], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) === false) {
            core::headerError();
            return ['error' => L('email incorrect format')];
        }
        if (!isset($param['phone'])) {
            core::headerError();
            return ['error' => L('phone not set')];
        }
        if (filter_var($param['phone'], FILTER_SANITIZE_NUMBER_INT) === false) {
            core::headerError();
            return ['error' => L('phone incorrect format')];
        }
        if (!isset($param['password'])) {
            core::headerError();
            return ['error' => L('password not set')];
        }
        if ($this->testlogin($param['login'])) {
            core::headerError();
            return ['error' => L('login in use')];
        }
        if ($this->testemail($param['email'])) {
            core::headerError();
            return ['error' => L('email in use')];
        }
        if ($this->testphone($param['phone'])) {
            core::headerError();
            return ['error' => L('phone in use')];
        }
        $param['password'] = password_hash($param['password'], PASSWORD_DEFAULT);
        $param['birthday'] = date('Y-m-d', strtotime($param['birthday']));
        if (!isset($param['role'])) {
            $param['role'] = 100;
        }

        return ['success' => $this->Set($param)];
    }

    public function Action_Add($param = null) {
        if ($ct = $this->CheckToken($param)) {
            return $ct;
        }
        if ($ct = $this->CheckUserToken($param)) {
            return $ct;
        }
        if (!isset($param['user'])) {
            core::headerError();
            return ['error' => L('Incorrect user data')];
        }
        $user = $param['user'];

        if (!isset($user['email'])) {
            core::headerError();
            return ['error' => L('email not set')];
        }
        if (filter_var(filter_var($user['email'], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) === false) {
            core::headerError();
            return ['error' => L('email incorrect format')];
        }
        if (!isset($user['phone'])) {
            core::headerError();
            return ['error' => L('phone not set')];
        }
        if (filter_var($user['phone'], FILTER_SANITIZE_NUMBER_INT) === false) {
            core::headerError();
            return ['error' => L('phone incorrect format')];
        }
        if (!isset($user['password'])) {
            core::headerError();
            return ['error' => L('password not set')];
        }
        if ($this->testlogin($user['login'])) {
            core::headerError();
            return ['error' => L('login in use')];
        }
        if ($this->testemail($user['email'])) {
            core::headerError();
            return ['error' => L('email in use')];
        }
        if ($this->testphone($user['phone'])) {
            core::headerError();
            return ['error' => L('phone in use')];
        }
//        $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);


        unset($user['usertoken']);
        $ret['success'] = $this->Action_Registre($user);
        $ret['accesstoken'] = core::guidtoken();
        Session::set('accesstoken', $ret['accesstoken']);
        return $ret;
    }

    public function Action_List($param = null) {
        if ($ct = $this->CheckToken($param)) {
            return $ct;
        }
        if ($ct = $this->CheckUserToken($param)) {
            return $ct;
        }

        $ret = $this->GetList($param);
        $ret['accesstoken'] = core::guidtoken();
        Session::set('accesstoken', $ret['accesstoken']);
        return $ret;
    }

    public function Action_Set($param = null) {
        if ($ct = $this->CheckToken($param)) {
            return $ct;
        }
        if ($ct = $this->CheckUserToken($param)) {
            return $ct;
        }
        if (!isset($param['user'])) {
            core::headerError();
            return ['error' => L('Incorrect user data')];
        }
        $user = $param['user'];

        unset($user['password']); // удаляем из массива пароль и созраняем данные пользователя не затрагивая сам пароль в базе
        unset($user['usertoken']);
        $ret['success'] = $this->Set($user);
        $ret['accesstoken'] = core::guidtoken();
        Session::set('accesstoken', $ret['accesstoken']);
        return $ret;
    }

    public function Action_Setrole($param = null) {
        if ($ct = $this->CheckToken($param)) {
            return $ct;
        }
        if ($ct = $this->CheckUserToken($param)) {
            return $ct;
        }
        if (!isset($param['userid'])) {
            core::headerError();
            return ['error' => L('Incorrect user id')];
        }
        if (!isset($param['roleid'])) {
            core::headerError();
            return ['error' => L('Incorrect user role id')];
        }
        $userid = $param['userid'];
        $roleid = $param['roleid'];
        $ret['success'] = $this->SetRole($userid, $roleid);
        $ret['accesstoken'] = core::guidtoken();
        Session::set('accesstoken', $ret['accesstoken']);
        return $ret;
    }


}
