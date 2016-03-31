<?php namespace App;

use Klein\Request;
use MongoDB\Database;
use Settings\AppSettings;

/**
 * Class Auth
 *
 * @package App
 */
class Auth
{

    public static function verifyToken(Request $req){

        if(isset($req->parsedBody)){
            $payload = $req->parsedBody;
        } else {
            $payload = $req->params();
        }


        if(isset($payload['token'])) {
            $token = $payload['token'];
        } else {
            return false;
        }

        if(isset($payload['username'])) {
            $username = $payload['username'];
        } else {
            return false;
        }

        if(isset($payload['timestamp'])) {
            $timestamp = $payload['timestamp'];
            if( time() - $timestamp < AppSettings::$token_lifetime) return false;
        } else {
            return false;
        }

        $userToken = hash_hmac('sha256', $username . AppSettings::$secret, AppSettings::$secret);


        $payload = json_encode($payload);
        $payload = implode("\n", [$req->method(), $req->uri(), $payload]);
        $ourToken = hash_hmac('sha256', $payload, $userToken);
        if($ourToken== $token) return true;

        return false;
    }

    public static function login(Request $req, Database $db)
    {
        $password = $req->paramsPost()->get('password');
        $username = $req->paramsPost()->get('username');

        $collection = $db->selectCollection('users');
        $row = $collection->findOne(['username' => $username]);

        if ($row && password_verify($password, $row->password))
        {
            $doc_array = (array)$row;
            $doc_array['_id'] = (string)$row->_id;
            //create a token
            $doc_array['timestamp'] = time();
            $doc_array['token'] = hash_hmac('sha256', $username . AppSettings::$secret, AppSettings::$secret);
            unset($doc_array['password']);

            return $doc_array;
        } else
        {
            return ['err' => 'User login failed',];
        }
    }
}