<?php

namespace rrdev\api;

use rrdev\models\user\UserModel;
use rrdev\core\Auth;

/**
 * Description of UserApiModel
 *
 * @author ivank
 */
class UserApiModel extends BaseApi {

    private $auth;
    private $user;

    public function __construct() {
        parent::__construct();
        $this->auth = new Auth();
        $this->user = new UserModel();
    }

    public function SignupAction($param) {
        $validParam = $this->auth->signup($param['data']);

        return $validParam;
    }
    public function LoginAction($param) {
        $retParam = $this->auth->login($param['data']['email'],$param['data']['password']);
        if(!$retParam){
            return ['error' => _('Wrong email or password')];
        }
        return ['success' => _('Its Ok')];
    }


}
