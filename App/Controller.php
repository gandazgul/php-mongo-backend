<?php namespace App;

use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\WriteConcern;

/**
 * Class Controller
 *
 * @package App
 */
class Controller
{
    /** @var Database */
    private $db;
    private $connection;

    public function __construct(Client $connection)
    {
        $this->connection = $connection;
        $this->db = $connection->selectDatabase(getenv('DB_DATABASE'));
    }

    /**
     * Prepares and returns a response for the $result
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     * @param $result
     * @return \Klein\AbstractResponse|Response
     */
    static function make_response(Request $req, Response $resp, ServiceProvider $service, $result)
    {
        if (isset($result['err']))
        {
            return $resp->code(500)->body($result['err'])->send();
        }

        $resp->header('Access-Control-Allow-Origin', getenv('BASE_URL'));

        $acceptHeader = new AcceptHeader($req->headers()->get('accept'));
        $acceptHeader = array_column($acceptHeader->getArrayCopy(), 'raw');

        if (in_array('text/html', $acceptHeader))
        {
            $service->render(ROOT . 'views/crud_riot.phtml');
            return false;
        }

        return $resp->json($result);
    }

    static function send_404(Request $req, Response $resp)
    {
        return $resp->code(404)->body("Entity not found");
    }

    /**
     * Parses the body for POST and PUT
     *
     * @param Request $req
     */
    public function parse_body(Request $req)
    {
        //klein doesnt support this so we are doing it manually
        $input = file_get_contents('php://input');

        switch ($req->headers()->get('Content-Type'))
        {
            case 'application/json':
                $req->parsedBody = json_decode($input, true);
                break;
            case 'application/x-www-form-urlencoded':
                parse_str($input, $req->parsedBody);
                break;
        }
    }

    function home(Request $req, Response $resp, ServiceProvider $service)
    {
        $service->render(ROOT . 'views/crud_riot.phtml');
    }

    function get_collection(Request $req, Response $resp, ServiceProvider $service)
    {
        $type = $req->param('type');

        $where = $req->paramsGet()->get('where', '[]');
        $query = json_decode($where, true);

        switch ($type)
        {
            case "test":
                $service->render(ROOT . 'views/test.phtml');

                return null;
                break;
        }

        $collection_con = $this->db->selectCollection($type);

        $collection = $collection_con->find($query);
        $result = [];
        foreach ($collection as $doc)
        {
            $doc_array = (array)$doc;
            $doc_array['_id'] = (string)$doc->_id;
            $result[] = $doc_array;
        }

        return static::make_response($req, $resp, $service, $result);
    }

    function get_entity_by_id(Request $req, Response $resp, ServiceProvider $service)
    {
        $type = $req->param('type');

        $collection_con = $this->db->selectCollection($type);

        $doc = $collection_con->findOne(['_id' => new ObjectID($req->param('id'))]);

        if ($doc)
        {
            $doc_array = (array)$doc;
            $doc_array['_id'] = (string)$doc->_id;

            return static::make_response($req, $resp, $service, $doc_array);
        }
        else
        {
            return static::send_404($req, $resp);
        }
    }

    function create_entity(Request $req, Response $resp, ServiceProvider $service)
    {
        $type = $req->param('type');
        $doc = $req->paramsPost()->all();
        if (!$doc)
        {
            $doc = $req->parsedBody;
        }

        // if any param named password encrypt
        if (isset($doc['password']))
        {
            $doc['password'] = password_hash($doc['password'], PASSWORD_DEFAULT);
        }

        $collection = $this->db->selectCollection($type);

        $insertResult = $collection->insertOne($doc, ['writeConcern' => new WriteConcern(1)]);

        $result = ['_id' => (string)$insertResult->getInsertedId()];
        if ($insertResult->getInsertedCount() <= 0)
        {
            $result['err'] = 'The insert failed';
        }

        return static::make_response($req, $resp, $service, $result);
    }

    function login(Request $req, Response $resp, ServiceProvider $service)
    {
        $result = Auth::login($req, $this->db);

        static::make_response($req, $resp, $service, $result);
    }

    function update_entity_by_id(Request $req, Response $resp, ServiceProvider $service)
    {
        $collection = $this->db->selectCollection($req->paramsNamed()->get('type'));
        $result = [];
        $updateResult = null;

        try
        {
            $doc = $req->parsedBody;
            unset($doc['_id']);
            $updateResult = $collection->replaceOne(
                ['_id' => new ObjectID($req->paramsNamed()->get('id'))],
                $doc,
                ['upsert' => true, 'multiple' => false, 'writeConcern' => new WriteConcern(1)]
            );
        }
        catch (BulkWriteException $e)
        {
            $writeError = $e->getWriteResult()->getWriteErrors()[0];
            $result['err'] = ($writeError->getMessage());
        }

        if ($updateResult && ($updateResult->getModifiedCount() + $updateResult->getUpsertedCount()) <= 0)
        {
            $result['err'] = 'The update failed';
        }

        return static::make_response($req, $resp, $service, $result);
    }

    function delete_entity_by_id(Request $req, Response $resp, ServiceProvider $service)
    {
        $collection = $this->db->selectCollection($req->paramsNamed()->get('type'));

        $deleteResult = $collection->deleteOne(['_id' => new ObjectID($req->paramsNamed()->get('id'))]);

        $result = [];
        if ($deleteResult->getDeletedCount() <= 0)
        {
            $result['err'] = 'The delete failed.';
        }

        return static::make_response($req, $resp, $service, $result);
    }
}