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
            $doc_array['token'] = hash_hmac('sha256', $username . AppSettings::$secret, AppSettings::$secret);
            unset($doc_array['password']);

            return $doc_array;
        } else
        {
            return ['err' => 'User login failed',];
        }
    }
}