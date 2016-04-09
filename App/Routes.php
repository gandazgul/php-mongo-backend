<?php namespace App;

use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;
use MongoDB\Client;
use function MongoDB\BSON\fromJSON;
use function MongoDB\BSON\toPHP;

/**
 * Class Routes
 *
 * @package App
 */
class Routes
{
    public static function init(Klein $app, Client $connection)
    {
        $controller = new Controller($connection);

        // middleware for parsing PUT requests
        $app->respond([$controller, 'parse_body']);

        //auth middleware
        //$app->respond([$controller, 'verifyToken']);


        //home
        $app->get('/', [$controller, 'home']);

        //auth
        $app->post('/auth/login', [$controller, 'login']);

        //collections routes
        //list collections
        $app->get('/collections', [$controller, 'get_collections']);
        //explicitly create new collection
        $app->post('/collections', [$controller, 'create_collection']);
        //drop a collection
        $app->delete('/collections/[a:id]', [$controller, 'delete_collection']);

        //Generic entity Crud
        $app->get('/[a:type]', [$controller, 'get_entity_set']);

        $app->get('/[a:type]/[a:id]', [$controller, 'get_entity_by_id']);

        $app->post('/[a:type]', [$controller, 'create_entity']);

        /**
         * This route will stand for PUT if it is not available on the client
         */
        $app->post(
            '/put/[a:type]/[a:id]', function (Request $req, Response $resp, ServiceProvider $service) use ($controller)
        {
            return $controller->update_entity_by_id($req, $resp, $service);
        });

        $app->put(
            '/[a:type]/[a:id]', function (Request $req, Response $resp, ServiceProvider $service) use ($controller)
        {
            return $controller->update_entity_by_id($req, $resp, $service);
        });

        /**
         * This route will stand for DELETE if it is not available on the client
         */
        $app->post(
            '/delete/[a:type]/[a:id]',
            function (Request $req, Response $resp, ServiceProvider $service) use ($controller)
        {
            return $controller->delete_entity_by_id($req, $resp, $service);
        });

        $app->delete(
            '/[a:type]/[a:id]', function (Request $req, Response $resp, ServiceProvider $service) use ($controller)
        {
            return $controller->delete_entity_by_id($req, $resp, $service);
        });
    }
}
