<?php namespace App;

use Klein\Exceptions\DispatchHaltedException;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Driver\Exception\BulkWriteException;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\WriteConcern;
use MongoDB\Model\CollectionInfo;
use Settings\AppSettings;
use Settings\DBSettings;

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
        $this->db = $connection->selectDatabase(DBSettings::$db_database);
    }

    /**
     * Prepares and returns a response for the $result
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     * @param array $result Defaults to empty
     *
     * @return \Klein\AbstractResponse|Response
     */
    static function make_response(Request $req, Response $resp, ServiceProvider $service, $result = [])
    {
        if (isset($result['err']))
        {
            $resp->code(500)->body($result['err'])->send();

            //ugly way of stopping klein from matching more routes because ->json sends the response
            throw new DispatchHaltedException(null, DispatchHaltedException::SKIP_REMAINING);
        }
        elseif (isset($result['code']) && $result['code'] != 200)
        {
            $resp->code($result['code']);
        }

        $resp->header('Access-Control-Allow-Origin', AppSettings::$base_url);

        $acceptHeader = new AcceptHeader($req->headers()->get('accept'));
        $acceptHeader = array_column($acceptHeader->getArrayCopy(), 'raw');

        if (in_array('text/html', $acceptHeader))
        {
            $service->render(ROOT . 'views/crud.phtml');

            return $resp;
        }

        $resp->json($result);

        //ugly way of stopping klein from matching more routes because ->json sends the response
        throw new DispatchHaltedException(null, DispatchHaltedException::SKIP_REMAINING);
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
                $parsed = [];
                parse_str($input, $parsed);

                $req->parsedBody = $parsed;
                break;
        }
    }

    /**
     * Lists collections, output is similar to a mongo document to make it easier to manage in the FE
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     */
    function get_collections(Request $req, Response $resp, ServiceProvider $service)
    {
        $collections = $this->db->listCollections(
            [
                "includeSystemCollections" => false,
                "filter" => [
                    'name' => [
                        '$regex' => '^(?!system\.).*',
                    ],
                ],
            ]
        );

        $collections = array_map(
            function (CollectionInfo $collection)
            {
                return [
                    // names are unique, need this to manage them on the FE
                    '_id' => $collection->getName(),
                    'name' => $collection->getName(),
                ];
            }, iterator_to_array($collections)
        );

        static::make_response($req, $resp, $service, $collections);
    }

    /**
     * Creates a new collection
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     */
    function create_collection(Request $req, Response $resp, ServiceProvider $service)
    {

        if (isset($req->parsedBody['name']))
        {
            $name = $req->parsedBody['name'];
            try
            {
                $this->db->createCollection($name);

                static::make_response($req, $resp, $service);
            }
            catch (RuntimeException $e)
            {
                static::make_response(
                    $req, $resp, $service, [
                        'err' => $e->getMessage(),
                    ]
                );
            }
        }
        else
        {
            static::make_response(
                $req, $resp, $service, [
                    'err' => "name is a required parameter to create a collection",
                ]
            );
        }
    }

    /**
     * Drops a collection
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     */
    function delete_collection(Request $req, Response $resp, ServiceProvider $service)
    {
        $name = $req->paramsNamed()->get('id', null);

        if ($name)
        {
            try
            {
                $this->db->dropCollection($name);

                static::make_response($req, $resp, $service);
            }
            catch (RuntimeException $e)
            {
                static::make_response(
                    $req, $resp, $service, [
                        'err' => $e->getMessage(),
                    ]
                );
            }
        }
        else
        {
            static::make_response(
                $req, $resp, $service, [
                    'err' => "name is a required parameter to create a collection",
                ]
            );
        }
    }

    /**
     * Renders the home page
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     */
    function home(Request $req, Response $resp, ServiceProvider $service)
    {
        $service->render(ROOT . 'views/home.phtml');
    }

    function get_entity_set(Request $req, Response $resp, ServiceProvider $service)
    {
        $type = $req->param('type');

        $where = $req->paramsGet()->get('where', '[]');
        $query = json_decode($where, true);

        $collection = $this->db->selectCollection($type);

        $set = $collection->find($query);
        $result = [];
        foreach ($set as $doc)
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

    /**
     * Attempt a login
     *
     * @param Request $req
     * @param Response $resp
     * @param ServiceProvider $service
     *
     * @return \Klein\AbstractResponse|Response
     */
    function login(Request $req, Response $resp, ServiceProvider $service)
    {
        $result = Auth::login($req, $this->db);

        if (!empty($result['err']))
        {
            return static::make_response(
                $req, $resp, $service, [
                    'error' => $result['err'],
                    'code' => 403,
                ]
            );
        }

        return static::make_response($req, $resp, $service, $result);
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

    function verifyToken(Request $req, Response $resp, ServiceProvider $service)
    {
        $result = Auth::verifyToken($req);

        if (!$result)
        {
            $resp->code(403);
        }

        static::make_response($req, $resp, $service, $result);
    }

}