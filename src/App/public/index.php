<?php
header("Content-type: text/html; charset=utf-8");
define('ROOT', str_replace("\\", '/', dirname(dirname(__FILE__))));
require ROOT . '/vendor/autoload.php';
$app = new \Sys\Core\Application();
$app->run(
    array(
        'appDir' => ROOT . '/app',  //自定义应用目录
        'routeRule' => array(    //支持路由重写等多种路由方式
            'rewrite' => array(
                '/pl' => '/product/list',
                '/my_product_list' => '/product/list',
            ),
            'regx' => array(),
        ),
    )
);

