<?php

/* 
 * 
 */

class Registry {

    protected $_items = [];

    public function __set($key, $value) {
        $this->_items[$key] = $value;
    }

    public function __get($key) {
        if (isset($this->_items[$key])) {
            return $this->_items[$key];
        } else {
            return null;
        }
    }

    public function __isset($key) {
        if (isset($this->_items[$key])) {
            return (false === empty($this->_items[$key]));
        } else {
            return null;
        }
    }

}
