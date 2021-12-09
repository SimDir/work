<?php

namespace rrdev\models\user;

use rrdev\core\Model;

defined('ROOT') OR die('No direct script access.');

class UserModel extends Model {

    public function getList($data = null) {
        if (!$data)
            return FALSE;
        $start = $data['start'] ? intval($data['start']) : 0;
        $limit = $data['limit'] ? intval($data['limit']) : 10;
        $list['tableName'] = $this->tableName;
        $list['count'] = $this->count($this->tableName);
//        $List['columns'] = $this->inspect($this->tableName);
        if (isset($data['orderby']) and $data['orderby'] != '') {
            $order['orderby'] = $data['orderby'];
            if ($data['dir'] != '') {
                $order['dir'] = $data['dir'];
            } else {
                $order['dir'] = 'ASC';
            }
        } else {
            $order = null;
        }
        $searchString = $data['searchString'];
        if ($searchString === '') {
            if (is_array($order)) {
                $tempbean = $this->findAll($this->tableName, 'ORDER BY ' . $order['orderby'] . ' ' . $order['dir'] . ' LIMIT ' . $start . ', ' . $limit);
            } else {
                $tempbean = $this->findAll($this->tableName, 'LIMIT ' . $start . ', ' . $limit);
            }
        } else {
            $tempbean = $this->getAll('SELECT * FROM ' . $this->tableName . " WHERE login LIKE '%" . $searchString . "%' OR firstname LIKE '%" . $searchString . "%' OR lastname LIKE '%" . $searchString . "%' OR email LIKE '%" . $searchString . "%'");
//            $tempbean = $this->findAll($this->tableName, 'LIMIT ' . $start . ', ' . $limit);
//            $tempbean = $this->findLike($this->tableName,['login'=>$searchString,
//                'email'=>$searchString
//                    ],' LIMIT ' . $start . ', ' . $limit);
        }
        if ($tempbean) {
//            $tempbean = $this->exportAll($tempbean, true);
            $list['data'] = $tempbean;
            return $list;
        }
        return FALSE;
    }

    public function setRole($userid, $roleid) {
        $tableuser = $this->load($this->tableName, $userid);
        $tablerole = $this->load('role', $roleid);
        $tableuser->sharedRoleList[] = $tablerole;
        return $this->store($tableuser);
    }

    public function usersCount() {
        return $this->count($this->tableName);
    }

    public function getUserFromEmail(string $email) {
        $u = $this->findOne($this->tableName, 'email = ?', array($email));
        if ($u) {
            return $u->export();
        }
        return null;
    }

    public function passwordEmailResetCode(int $userID, string $Code) {
        $user = $this->findOne($this->tableName, 'id = ?', array($userID));

        $user->resetpass = $Code;

        return $this->store($user);
    }

    public function passwordEmailResetDeleteCode(int $userID) {
        $user = $this->findOne($this->tableName, 'id = ?', array($userID));

        $user->resetpass = null;

        return $this->store($user);
    }

    public function getUserFromToken(string $UserToken) {
        $user = $this->findOne($this->tableName, '(token = :token)', [':token' => $UserToken]);
        if ($user) {
            return $user->export();
        } else {
            return null;
        }
    }

    public function testemail(string $email): bool {
        if ($this->count($this->tableName, ' WHERE email = ?', [$email]) > 0)
            return true;
        return false;
    }

    public function testphone(string $phone): bool {
        if ($this->count($this->tableName, ' WHERE phone = ?', [$phone]) > 0)
            return true;
        return false;
    }

    public function testlogin(string $login): bool {
        if ($this->count($this->tableName, ' WHERE login = ?', [$login]) > 0)
            return true;
        return false;
    }

}
