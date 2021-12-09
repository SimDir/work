<?php

/**
 * Copyright 2021 Ivan P. Kolotilkin
 * 
 * logic@xaker.ru
 * 
 * +79372796383
 * 
 * https://vk.com/id131505651
 * 
 * https://github.com/SimDir/rrdevcrm
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

define('TIME_START', microtime(true));// для подсчета времени работы скрипта
define('USE_MEM', memory_get_usage()); // тоже самое только для используемой памяти сервера
if (version_compare(phpversion(), '7.4.0', '<') == true) {
    die(_('On the server PHP version is less than 7.4.0 it is impossible to continue. update PHP version').PHP_EOL);
//            'На сервере версия PHP ниже 7.4.0 продолжить невозможно. обновить версию PHP');
}
//ini_set('memory_limit', -1);
define('TIMEZONE', 'Europe/Ulyanovsk');

define('DS', DIRECTORY_SEPARATOR); // разделитель для путей к файлам
define('ROOT', dirname(__FILE__)); // защита всех файлов приложения от прямого доступа к ним
define('SITE_DIR', realpath(dirname(__FILE__)) . DS); // путь к корневой папке сайта getcwd()
define('APP', SITE_DIR . 'app' . DS); // путь к приложению
define('TEMPLATE_DIR', SITE_DIR . 'templates' . DS);

define('CONFIG_DIR', SITE_DIR . 'config' . DS); // папка с конфигами

define('SHOW_ERROR', true); // Показывать ошибки контроллеров или перенаправлять на 404 страницу? https://natribu.org/ru/
define('ERROR_URL', '/404.html');


define('SESSION_PREFIX', 'd_session_b');
define('SESSION_DIR', SITE_DIR . 'usersessions'); // false если не нужно использовать дирикторию а оставить на усмотрение сервера
define('SESSION_DRIVER', 'database');

// Check if SSL
if ((isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) || $_SERVER['SERVER_PORT'] == 443) {
    $protocol = 'https://';
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $protocol = 'https://';
} else {
    $protocol = 'http://';
}
define('HTTP_SERVER', $protocol . $_SERVER['HTTP_HOST'] .'/');//. rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\'));
//die(HTTP_SERVER);

define('COMPOSER', SITE_DIR . 'vendor' . DS . 'autoload.php');
if (file_exists(COMPOSER)) {
    require_once COMPOSER; // подключаем композер
} else {
    die('на сервере отсутствует дириктория "vendor" а это значит что "Composer" не установлен! продолжить невозможно. установите composer и обновите бибилиотеки. https://getcomposer.org/');
}
rrdev\core::Run();
