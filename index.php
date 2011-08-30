<?php

/* Include 'some' of the key files */
define("ROOT", __DIR__.'/');#
define('SYSROOT', ROOT.'System/');
ini_set("display_errors", "On");

include ROOT.'/System/config/config.php';

function index($req)
{
    return "ololo";
}


$app_params = array(
    'root' => ROOT,
    'url_root' => '/auth/',
    'template' => SYSROOT.'templates/api/xml/'
);


$app = new Application($app_params);

#$app->Get('/', 'index');

$app->LoadPlugins('System/plugins/main/');

$app->Run();