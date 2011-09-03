<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(!defined('PLUGIN_LOADER'))
    return false;

foreach(glob(__DIR__."/handlers/*.php") as $handler)
{
    require_once $handler;
}

function login($req, $login, $password, $apiId, $sign)
{
    $af = new AccountManager();
    $acc = $af->Login($login, $password);
    switch($acc['Code'])
    {
        case ApiErrorCode::AuthAccountNotFound: return api_error($acc['Code'], 'Account not found. Invalid login or password');
        case ApiErrorCode::AuthAlreadyAuthorized: return api_error($acc['Code'], 'Account already authorized', array('Hash' => $acc['Hash']));
        case ApiErrorCode::AuthError: return api_error($acc['Code'], 'Authorization error');
        default: return api_response(array('Hash' => $acc));
    }
}

function getAccount($req, $hash, $apiId, $sign)
{
    $af = new AccountManager();
    $db = SystemConfig::GetDatabaseInstance();
    $db->Query("SELECT `AccountId` FROM `Authorized` WHERE `Hash`=?", $hash);
    $accountId = $db->Assoc('AccountId');
    $acc = $af->GetAccountById($accountId);
    if($acc)
    {
        $db->Query("SELECT `Secret` FROM `Apps` WHERE `Id`=?d", $api);
        $key = $db->Assoc('Secret');
        $acc = encrypt(toXml($acc), $key);
        return api_response(array('Account' => $acc));
    }
}

function logout($req, $hash, $apiId, $sign)
{
    $af = new AccountManager();
    $result = $af->Logout($hash);
    if($result)
    {
        return api_response(array('LoggedOut' => $result));
    }
    else
    {
        return api_error(ApiErrorCode::AuthAlreadyLoggedOut, 'Account already logged out!');
    }
}

$app = Application::GetInstance();
$app->Get('login/{login:str}/{password:str}', 'login');
$app->Get('getObject/account/{hash:str}', 'getAccount');
$app->Get('logout/{hash:str}', 'logout');
$app->AppendPath('/{apiId:int}/{hash}', 3);

#dump($app->Requests["GET"]);

?>
