<?php

namespace rrdev\core;

defined('ROOT') OR die('No direct script access.');

/**
 * Description of Model
 *
 * @author Ivan P Kolotilkin
 * 
 * https://youtu.be/iU8zlbkpwyo
 */
use RedBeanPHP\Facade as R;

class Model extends R {

    public $tableName = '';

    public function __construct() {
        if ($this->tableName === '') {
            $a = explode('\\', get_class($this));
            $this->tableName = mb_strtolower(str_replace('Model', '', end($a)));
        }

        if (!is_null($this->getWriter())) {
            return $this;
        }
        $incFile = CONFIG_DIR . 'DataBase.php';
        $host = '';
        $port = '';
        $dbname = '';
        $login = '';
        $pass = '';
        if (file_exists($incFile)) {
            $Config = include($incFile);

            $host = $Config['db_host'];
            $port = $Config['db_port'];
            $dbname = $Config['db_name'];
            $login = $Config['db_login'];
            $pass = $Config['db_pass'];
        } else {
            $Config['db_driver'] = 'SQLite';
            $Config['db_frozen'] = false;
        }

        switch (strtolower($Config['db_driver'])) {
            case "mariadb":
                $this->setup("mysql:host=$host:$port;dbname=$dbname", $login, $pass);
                break;
            case "postgresql":
                $this->setup("pgsql:host=$host:$port;dbname=$dbname", $login, $pass);
                break;
            case "sqlite":
                $this->setup('sqlite:' . APP . 'database.db');
                break;
            case "cubrid":
                $this->setup("cubrid:host=$host;port=$port;dbname=$dbname", $login, $pass);
                break;
        }
        if (!$this->testConnection()) {
//            $this->fancyDebug(false);
            die("ошибка бaзы данных $host:$port. неудалось установить соединение c БД $dbname");
//            throw new \Exception(__METHOD__ . " ошибка бaзы данных $host:$port. неудалось установить соединение c БД $dbname");
        }
        //for version 5.3 and higher
        //optional but recommended
        $this->useFeatureSet('novice/latest');
        $this->useJSONFeatures(true);
        $this->freeze($Config['db_frozen']);
        R::ext("xDispense", function ($table_name) {
            return R::getRedBean()->dispense($table_name);
        });
    }

//    public function __destruct() {
//        $this->close();
//    }

    public function xDispense($table_name) {
        return R::getRedBean()->dispense($table_name);
    }

//    public static function dispense($typeOrBeanArray, $num = 1, $alwaysReturnArray = FALSE) {
//        if (is_array($typeOrBeanArray)) {
//            if (!isset($typeOrBeanArray['_type']))
//                throw new RedException('Missing _type field.');
//            $import = $typeOrBeanArray;
//            $type = $import['_type'];
//            unset($import['_type']);
//        } else {
//            $type = $typeOrBeanArray;
//        }
//
//        if (!preg_match('/^[a-z0-9_]+$/', $type)) {
//            throw new RedException('Invalid type: ' . $type);
//        }
//
//        $redbean = parent::getRedBean();
//        $beanOrBeans = $redbean->dispense($type, $num, $alwaysReturnArray);
//
//        if (isset($import)) {
//            $beanOrBeans->import($import);
//        }
//
//        return $beanOrBeans;
//    }

    public function set($data = null) {
        if (!$data)
            return FALSE;
        if (isset($data['id'])) {
            $table = $this->load($this->tableName, intval($data['id']));
            unset($data['id']);
            $table->import($data);
            $table->updatedatetime = date('Y-m-d H:i:s');
            return $this->store($table);
        } else {
            $table = $this->dispense($this->tableName);
            $table->createdatetime = date('Y-m-d H:i:s');
            $table->import($data);
            return $this->store($table);
        }
        return FALSE;
    }

    public function getById($id) {
        if (!$id)
            return null;
        return $this->load($this->tableName, intval($id));
    }

    public function getList($data = null) {
        if (!$data)
            return FALSE;
        $start = $data['start'] ? intval($data['start']) : 0;
        $limit = $data['limit'] ? intval($data['limit']) : 10;
        $list['count'] = $this->count($this->tableName);
//        $list['columns'] = $this->inspect($this->tableName);
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

        if (is_array($order)) {
            $tempbean = $this->findAll($this->tableName, 'ORDER BY ' . $order['orderby'] . ' ' . $order['dir'] . ' LIMIT ' . $start . ', ' . $limit);
        } else {
            $tempbean = $this->findAll($this->tableName, 'LIMIT ' . $start . ', ' . $limit);
        }
        if ($tempbean) {
//            $tempbean = $this->exportAll($tempbean, true);
            $list['data'] = $tempbean;
            return $list;
        }
        return FALSE;
    }

    public function dellete($data = null) {
        if (!$data)
            return FALSE;
        return $this->trash($this->load($this->tableName, $data['id']));
    }

}
