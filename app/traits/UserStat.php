<?php

/*
 *  реализуем на сайте счетчик онлайн посетителей.
 */

R::setup('mysql:host=127.0.0.1;dbname=count', 'root', '');  // подключаемся к базе данных

if (!R::testconnection()) {
    exit('Нет соединения с базой данных');
}

$cookie_key = 'online-cache';

// Достается ip пользователя через суперглобальный массив SERVER
$ip = $_SERVER['REMOTE_ADDR'];

// Проверяет нет ли уже такой записи об этом пользователе, чтобы каждый раз её не дублировать
$online = R::findOne('online', 'ip = ?', array($ip));

if ($online) {
    $do_update = false;
    // Если такой пользователь уже найден, то мы его обновляем,
    // но это будет сильным ударом по производительности, поэтому использует куки
    if (CookieManager::stored($cookie_key)) {
        $c = (array) @json_decode(CookieManager::read($cookie_key), true);
        if ($c) {
            //обновляем данные в базе каждые 5 минут
            if ($c['lastvisit'] < (time() - (60 * 5))) {
                $do_update = true;
            }
        } else {
            $do_update = true;
        }
    } else {
        $do_update = true;
    }
    if ($do_update) {
        // Сохраним в куки дату последнего обновления 
        // информации о посещении пользователя
        $time = time();
        $online->lastvisit = $time;
        R::store($online);
        CookieManager::store($cookie_key, json_encode(array(
            'id' => $online->id,
            'lastvisit' => $time)));
    }
} else {
    // Если пользователь не найден, то мы его добавим
    $time = time();
    $online = R::dispense('online');
    $online->lastvisit = $time;
    $online->ip = $ip;
    R::store($online);
    CookieManager::store($cookie_key, json_encode(array(
        'id' => $online->id,
        'lastvisit' => $time)));
    // json_encode мы делаем потому что в куки нельзя хранить структуры, 
    // в отличии от сессии, а можно хранить только строки
}
 
// Выводим количество онлайн за последний час
$online_count = R::count('online', "lastvisit > " . ( time() - (3600) ));