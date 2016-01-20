<?php

use Klein\Request;
use Klein\Response;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Driver\WriteConcern;

require_once ROOT . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(ROOT);
$dotenv->load();

$app = new \Klein\Klein();

$connection = new Client("mongodb://" . getenv('DB_HOST') . ":" . getenv('DB_PORT'));
$db = $connection->selectDatabase(getenv('DB_DATABASE'));

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
    $collection_con = $db->selectCollection($req->param('type'));

    $collection = $collection_con->find();
    $result = [];
    foreach ($collection as $doc)
    {
        $doc_array = (array)$doc;
        $doc_array['_id'] = (string)$doc->_id;
        $result[] = $doc_array;
    }

    return make_response($resp, $result);
});

$app->get('/[:type]/[:id]', function (Request $req, Response $resp) use ($db)
{
    $type = $req->param('type');
    $collection_con = $db->selectCollection($type);

    $doc = $collection_con->findOne(['_id' => new ObjectID($req->param('id'))]);

    $doc_array = (array)$doc;
    $doc_array['_id'] = (string)$doc->_id;
    $result = $doc_array;

    return make_response($resp, $result);
});

$app->post('/[:type]', function (Request $req, Response $resp) use ($db)
{
    $doc = $req->paramsPost()->all();
    $type = $req->param('type');
    $collection = $db->selectCollection($type);

    $insertResult = $collection->insertOne($doc, ['writeConcern' => new WriteConcern(1)]);
    $result = ['_id' => (string)$insertResult->getInsertedId()];
    if ($insertResult->getInsertedCount() <= 0)
    {
        $result['err'] = 'The insert failed';
    }

    return make_response($resp, $result);
});

function put($type, $id, Request $req, Response $resp)
{
    global $db;

    //klein doesnt support this
    parse_str(file_get_contents('php://input'), $doc);
    $collection = $db->selectCollection($type);

    $updateResult = $collection->replaceOne(['_id' => new ObjectID($id)], $doc, ['upsert' => true, 'multiple' => false, 'writeConcern' => new WriteConcern(1)]);
    $result = [];
    if (($updateResult->getModifiedCount() + $updateResult->getUpsertedCount()) <= 0)
    {
        $result['err'] = 'The update failed';
    }
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

    $collection = $db->selectCollection($type);

    $deleteResult = $collection->deleteOne(['_id' => new ObjectID($id)]);

    $result = [];
    if ($deleteResult->getDeletedCount() <= 0)
    {
        $result['err'] = 'The delete failed.';
    }

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