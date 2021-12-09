<?php

namespace rrdev\core;

use rrdev\models\user\UserModel;
use rrdev\core\session\SessionManager;

/**
 * Description of Auth
 *
 * @author ivank
 */
class Auth {

    private $user;
    private $userId;
    private $sessionId;

    public function __construct() {
        $this->user = new UserModel();
        $this->userId = SessionManager::get('userId');
        $this->sessionId = SessionManager::id();
//        dd($this->user);
    }

    public function user() {
        $user = $this->user->findOne($this->user->tableName, 'id = :id', [':id' => $this->userId]);
        if (!$user) {
            return null;
        }
        return $user->export();
    }

    public function signup($userData) {
        $validUserData = $this->validateUserData($userData);
        if (array_key_exists('error', $validUserData)) {
            return $validUserData;
        }
        $userId = $this->addUser($validUserData);
        return ['success' => _('user is created'), 'userId' => $userId];
    }

    public function addUser($userData) {
        if (!$this->passwordIsHash($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        $userData['birthday'] = date('Y-m-d', strtotime($userData['birthday']));
        return $this->user->Set($userData);
    }

    public function saveUser($userData) {
        unset($userData['password']);
        unset($userData['lastlogin']);
        unset($userData['token']);
        unset($userData['createdatetime']);
        return $this->user->Set($userData);
    }

    public function setPwd($newPass) {
        if (!$this->checkPassword($newPass, $errors)) {
            return $errors;
        }
        $userData = $this->user();
        unset($userData['password']);
        unset($userData['lastlogin']);
        unset($userData['token']);
        unset($userData['createdatetime']);
        $userData['password'] = $newPass;
        if (!$this->passwordIsHash($newPass)) {
            $userData['password'] = password_hash($newPass, PASSWORD_DEFAULT);
        }
        return $this->user->Set($userData);
    }

    public function login($email, $password) {
        $user = $this->user->findOne($this->user->tableName, ' email = :email ', [':email' => $email]);

        if ($user) {
            //логин существует
            if (password_verify($password, $user->password)) {
                //если пароль совпадает, то нужно авторизовать пользователя
                $user->lastlogin = date('Y-m-d H:i:s');
//                $user->browser = $_SERVER['HTTP_USER_AGENT'];
//                $user->ip = $_SERVER['REMOTE_ADDR'];
                $user->token = $this->UniqIdReal();
                $this->user->store($user);
                SessionManager::set('userId', $user->id);
                return true;
            }
        }

        return false;
    }

    private function validateUserData($param = null): array {

        if (!isset($param['email'])) {
            return ['error' => _('email not set')];
        }
        if (filter_var(filter_var($param['email'], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) === false) {
            return ['error' => _('email incorrect format')];
        }
        if ($this->user->testemail($param['email'])) {
            return ['error' => _('email in use')];
        }
        if (!isset($param['phone'])) {
            return ['error' => _('phone not set')];
        }
        if (filter_var($param['phone'], FILTER_SANITIZE_NUMBER_INT) === false) {
            return ['error' => _('phone incorrect format')];
        }
        if (!isset($param['password'])) {
            return ['error' => _('password not set')];
        }
        if (!$this->checkPassword($param['password'], $errors)) {
            $errorText = '';
            foreach ($errors as $value) {
                $errorText .= $value . '! ' . PHP_EOL;
            }
            return ['error' => _('Password incorrect format') . '! ' . PHP_EOL . $errorText, 'errorstext' => $errors];
        }
        if ($this->user->testphone($param['phone'])) {
            return ['error' => _('phone in use')];
        }

        return $param;
    }

    private function checkPassword($pwd, &$errors) {
        $errors_init = $errors;

        if (strlen($pwd) < 8) {
            $errors[] = _('Password too short');
        }

        if (!preg_match("#[0-9]+#", $pwd)) {
            $errors[] = _('Password must include at least one number');
        }

        if (!preg_match("#[a-zA-Z]+#", $pwd)) {
            $errors[] = _('Password must include at least one letter');
        }

        return ($errors == $errors_init);
    }

    public function generatePassword($length = 8) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*+';
        $str = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++)
            $str .= $chars[random_int(0, $max)];
        return $str;
    }

    private function passwordIsHash($password) {
        $nfo = password_get_info($password);
        return $nfo['algo'] != 0;
    }

    public function generateNewTokenToUser($UserId) {
        $token = $this->uniqIdReal();
        $User = $this->user->load($this->user->tableName, $UserId);
        $User->token = $token;
        $this->user->store($User);
        return $token;
    }

    /**
     * Генерируеут рандомный UID
     * https://www.php.net/manual/ru/function.uniqid.php#120123
     */
    public function uniqIdReal($lenght = 64) {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } elseif (function_exists('random_bytes')) {
            $bytes = random_bytes(ceil($lenght / 2));
        } else {
            throw new Exception(_('No cryptographically secure random function available'));
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

}
