<?php

defined('ROOT') OR die('No direct script access.');

return [
    '^page/([-_a-zA-Z0-9]+)/([-_a-zA-Z0-9]+.html)' => 'page/get/$2/$1',
    '^page/([-_a-zA-Z0-9]+.html)' => 'page/get/$1',
    '^produkt/([-_a-zA-Z0-9]+)' => 'catalog/produkt/$1',
    '^category/([-_a-zA-Z0-9]+)' => 'catalog/category/$1',
    '([-_a-zA-Z0-9]+.html)' => 'page/get/$1',
//    '\bjs\b/([-_a-z0-9]+)' => 'res/js/$1',
//    '\bimg\b' => 'res/img/',
//    '\bimg\b/([-_a-z0-9]+)' => function () {
//        return 'dd';
//    }
];
