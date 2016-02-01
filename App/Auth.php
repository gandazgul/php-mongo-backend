<?php namespace App;

use Klein\Request;
use MongoDB\Database;

/**
 * Class Auth
 *
 * @package App
 */
class Auth
{
    public static function login(Request $req, Database $db)
    {
        $params = $req->paramsPost()->all();
        //$password = password_hash($params['password'], PASSWORD_DEFAULT);

        $collection = $db->selectCollection('users');
        $row = $collection->findOne(['user' => $params['user']]);

        if ($row && password_verify($params['password'], $row->password))
        {
            $doc_array = (array)$row;
            $doc_array['_id'] = (string)$row->_id;

            return $doc_array;
        } else
        {
            return ['err' => 'User login failed',];
        }
    }
}