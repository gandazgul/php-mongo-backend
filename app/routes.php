<?php

use Klein\Request;
use Klein\Response;

require_once ROOT . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$app = new \Klein\Klein();

$connection = new MongoClient("mongodb://" . getenv('DB_HOST') . ":" . getenv('DB_PORT'));
$db = $connection->{getenv('DB_DATABASE')};

function make_response(Response $resp, $result)
{
    if (isset($result['err']))
    {
        return $resp->code(500)->body($result['err'])->send();
    }

    return $resp->json($result);
}

$app->get('/', function ()
{
    return 'SCT Generic Backend';
});

$app->get('/[:type]', function (Request $req, Response $resp) use ($db)
{
    $collection = $db->{$req->param('type')};

    $result = $collection->find();

    return make_response($resp, iterator_to_array($result));
});

$app->get('/[:type]/[:id]', function (Request $req, Response $resp) use ($db)
{
    $type = $req->param('type');
    $collection = $db->{$type};

    $result = $collection->findOne(['_id' => new MongoId($req->param('id'))]);

    return make_response($resp, $result);
});

$app->post('/[:type]', function (Request $req, Response $resp) use ($db)
{
    $doc = $req->paramsPost()->all();

    $collection = $db->{$type};

    $result = $collection->insert($doc, ['w' => 1]);

    return make_response($resp, $result);
});

function put($type, $id, Request $req, Response $resp)
{
    global $db;

    $doc = $req->paramsPost()->all();
    $collection = $db->{$type};

    $result = $collection->update(['_id' => new MongoId($id)], $doc, ['upsert' => true, 'multiple' => false, 'w' => 1]);

    return make_response($resp, $result);
}

/**
 * This function will stand for put if PUT is not available on the client
 */
$app->post('/put/[:type]/[:id]', function (Request $req, Response $resp)
{
    return put($req->param('type'), $req->param('id'), $req, $resp);
});

$app->put('/[:type]/[:id]', function (Request $req, Response $resp)
{
    return put($req->param('type'), $req->param('id'), $req, $resp);
});

function delete($type, $id, Response $resp)
{
    global $db;

    $collection = $db->{$type};

    $result = $collection->remove(['_id' => new MongoId($id)]);

    return make_response($resp, $result);
}

/**
 * This function will stand for delete if DELETE is not available on the client
 */
$app->post('/delete/[:type]/[:id]', function (Request $req, Response $resp)
{
    return delete($req->param('type'), $req->param('id'), $resp);
});

$app->delete('/[:type]/[:id]', function (Request $req, Response $resp)
{
    return delete($req->param('type'), $req->param('id'), $resp);
});


$app->dispatch();