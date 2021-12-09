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

    public function IndexAction($param = null) {
        $LoggedUser = $this->auth->user();
//        dd($LoggedUser);
        if (is_null($LoggedUser)) {
//            return $this->view->Render('userform.html');
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
//            dd($user, $this->data);
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
//                dd($setPwdRet);
            }
        }
        return core::Redirect('/user/');
    }

    public function SignupAction($param = null) {

        return $this->view->Render('userform.html');
    }

    public function LoginAction($param = null) {

        return $this->view->Render('userform.html');
    }

    public function LogoutAction($param = null) {
        SessionManager::destroyAll();
        return core::Redirect('/user/login');
//        return $this->View->Render();
    }

    public function SetpasswordAction() {
        $DataUser = $this->REQUEST;
        $fUser = $this->user->GetUserFromCode($DataUser['code']);
        if ($fUser) {
//            mvcrb::Redirect('/lk/');
            $this->user->PasswordCodeEmailDele($fUser['id']);
            return ['success' => $this->user->ChangePassword($fUser['id'], $DataUser['password'])];
        }
        return ['error' => "пользователь не найден"];
    }

    public function PaswdAction($UniqId) {
        $fUser = $this->user->GetUserFromCode($UniqId);
        if ($fUser) {
            $this->View->code = $UniqId;
            $this->View->userid = $fUser['id'];
            return $this->View->execute('codepassword.html');
        }
//        $this->View->content = $this->View->execute('nofinduser.html');
        return core::Redirect('/'); //$this->View->execute('index.html', TEMPLATE_DIR);
    }

    public function ForgotPasswordAction() {

        if ($this->POST) {
            $email = $this->REQUEST['email'];
//            dd($email);
            if ($this->user->testemail($email)) {
//                dd($email);
                $User = $this->user->GetUserFromEmail($email);
                $UniqId = $this->user->UniqIdReal();
                $subject = 'Восстановление пароля';
                $message = 'Вы нажали восстановить пароль' . PHP_EOL;
                $message .= "Что-бы восстановить ваш пароль пройдите по ссылке  " . HTTP_SERVER . "/user/paswd/$UniqId" . PHP_EOL;
                $message .= "или нажмите   <a href=\"" . HTTP_SERVER . "/user/paswd/$UniqId\">Сюда</a> Что-бы восстановить ваш пароль" . PHP_EOL;
                $headers = 'From: admin@agatech.ru' . "\r\n" .
//                        'Reply-To: support@agatech.ru' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

//                $ret = rr_mail($email, $subject, $message, $headers);
                $fos = new FosModel();
                $fosData['email'] = $email;
                $fosData['subject'] = $subject;
                $fosData['message'] = $message;

                $ret = $fos->Send($fosData);

                if (isset($ret['success'])) {

                    $ret = $this->user->PasswordCodeEmailReset(intval($User['id']), $UniqId);
//                    dd($ret);
                    return ['success' => $ret];
                }
                return ['error' => 'Ошибка сервера во время отправки пароля, пароль не изменен. обратитесь к администрации'];
            }
            return ['error' => "пользователь с $email не найден"];
        }
        return $this->View->Render('forgotpassword.html');
    }

    public function AddAction($param = "user") {
        $user = new UserModel();
        $lu = $user->LoggedUser();

        if (!is_null($lu)) {
            return _('The initial setup system is disabled');
        }
        $uc = $user->UserCount();
        if ($uc !== 0) {
            return _('The initial setup system is disabled');
        }
        $data['password'] = password_hash('123456', PASSWORD_DEFAULT);
        $data['birthday'] = date('Y-m-d', strtotime('1 March 1985'));
        $data['role'] = 900;
        $data['email'] = 'logic@xaker.ru';
        $user->set($data);
//        $uc = $user->UserCount();
//                dd($uc);
        return _('Setup system is success');
    }

}
