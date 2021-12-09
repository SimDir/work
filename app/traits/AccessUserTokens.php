<?php

namespace rrdev;

defined('ROOT') OR die('No direct script access.');

/**
 * Проверка токенов доступа пользователя
 * используется в моделях
 *
 * @author Ivan P Kolotilkin
 */
trait AccessUserTokens {

    private function CheckToken($param) {
        if (!isset($param['accesstoken'])) {
            core::headerError();
            return ['error' => L('Incorrect access token provided')];
        }
        if ($param['accesstoken'] != Session::get('accesstoken')) {
            core::headerError();
            return ['error' => L('Connection unsuccessful incorrect access token provided')];
        }
        return false;
    }

    private function CheckUserToken($param) {
        if (!isset($param['usertoken'])) {
            core::headerError();
            return ['error' => L('Incorrect user token provided')];
        }

        if ($param['usertoken'] === '') {
            core::headerError();
            return ['error' => L('Connection unsuccessful incorrect user token provided')];
        }

        return false;
    }

}
