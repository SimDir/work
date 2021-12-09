<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace rrdev\api;

/**
 * Description of BaseApi
 *
 * @author ivank
 */
class BaseApi {

    public $requestUri = [];
    public $requestParams = [];
    public $REQUEST_METHOD;

    public function __construct() {

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("x-powered-by: PHP/9.9.8");

        //Массив GET параметров разделенных слешем
        $this->requestUri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        $this->requestParams = $_REQUEST;

        //Определение метода запроса
        if ($this->REQUEST_METHOD == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->REQUEST_METHOD = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->REQUEST_METHOD = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }
    }

}
