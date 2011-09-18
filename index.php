<?php

/* Include 'some' of the key files */
define("ROOT", __DIR__.'/');#
define('SYSROOT', ROOT.'System/');
ini_set("display_errors", "On");

include ROOT.'/System/config/config.php';

function index($req)
{
    return render_to_response(array('what?'));
}


$app_params = array(
    'root' => ROOT,
    'url_root' => '/k5/',
    'template' => SYSROOT.'templates/'
);


$app = new Application($app_params);

$app->Get('', 'index');

$app->LoadPlugins('System/plugins/main/');
dump(Application::GetInstance());

$app->Run();