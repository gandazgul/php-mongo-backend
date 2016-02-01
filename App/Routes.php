<?php namespace App;

use Klein\Klein;
use Klein\Request;
use Klein\Response;
use MongoDB\Database;
use function MongoDB\BSON\fromJSON;
use function MongoDB\BSON\toPHP;

/**
 * Class Routes
 *
 * @package App
 */
class Routes
{
    public static function init(Klein $app, Database $db)
    {
        $controller = new Controller($db);

        $app->get('/', function ()
        {
            return 'SCT Generic Backend';
        });

        $app->get('/[a:type]', [$controller, 'get_collection']);

        $app->get('/[a:type]/[a:id]', [$controller, 'get_entity_by_id']);

        $app->post('/auth/login', [$controller, 'login']);

        $app->post('/[a:type]', [$controller, 'create_entity']);

        /**
         * This route will stand for put if PUT is not available on the client
         */
        $app->post('/put/[a:type]/[a:id]', function (Request $req, Response $resp) use ($controller)
        {
            return $controller->update_entity_by_id($req->param('type'), $req->param('id'), $req, $resp);
        });

        $app->put('/[a:type]/[a:id]', function (Request $req, Response $resp) use ($controller)
        {
            return $controller->update_entity_by_id($req->param('type'), $req->param('id'), $req, $resp);
        });

        /**
         * This route will stand for delete if DELETE is not available on the client
         */
        $app->post('/delete/[a:type]/[a:id]', function (Request $req, Response $resp) use ($controller)
        {
            return $controller->delete_entity_by_id($req->param('type'), $req->param('id'), $resp);
        });

        $app->delete('/[a:type]/[a:id]', function (Request $req, Response $resp) use ($controller)
        {
            return $controller->delete_entity_by_id($req->param('type'), $req->param('id'), $resp);
        });
    }
}
