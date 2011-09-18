<?php

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
    const AuthAccountNotFound = 202;
    const AuthAccountInvalidLoginPass = 203;
    const AuthAlreadyAuthorized = 204;
    const AuthAlreadyLoggedOut = 205;
    
    const RegAccountAlreadyExists = 300;
    const RegInvalidLogin = 301;
    const RegInvalidPassword = 302;
    const RegInvalidEmail = 303;
    const RegInvalidFirstName = 304;
    const RegInvalidLastName = 305;
    
    const RequestError = 400;
    const RequestTooFast = 401;
    const RequestInvalidSign = 402;
    
    const ServerError = 500;
}

?>
