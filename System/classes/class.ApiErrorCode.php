<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiErrorCode
 *
 * @author nolka
 */
class ApiErrorCode
{
    const MethodNotSpecified = 100;
    const MethodNotExists = 101;
    const MethodInvalidArgs = 102;
    
    const AuthError = 200;
    const AuthInvalidSign = 201;
    const AuthAccountNotFound = 202;
    const AuthAccountInvalidLoginPass = 203;
    const AuthAlreadyAuthorized = 204;
    const AuthAlreadyLoggedOut = 205;
    
    const RequestError = 300;
    const RequestTooFast = 301;
    
    const ServerError = 500;
}

?>
