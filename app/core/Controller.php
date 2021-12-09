<?php

namespace rrdev\core;

defined('ROOT') OR die('No direct script access.');

/**
 * Description of Controller
 *
 * @author Ivan P Kolotilkin
 */
abstract class Controller {

    public $REQUEST_METHOD = FALSE;

    public function __construct($param=null) {
        $this->REQUEST_METHOD = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_ENCODED);
        return $this;
    }

    public function __get(string $name) {
        switch (strtolower($name)) {
            case 'view':
                $arrThisClassName = explode('\\', get_class($this));
                return View::getInstance(end($arrThisClassName));
            case 'json':
                return json_decode(file_get_contents('php://input'), true);
            case 'data':
                $data = json_decode(file_get_contents('php://input'), true);
                $req = array_merge($_POST,$_GET);
                if(is_array($data)){
                    $all = array_merge($req,$data);
                    return $all;
                }
                return $req;
                
            default:
                return null;
        }
    }



}
