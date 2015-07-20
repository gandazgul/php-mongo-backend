# php-mongo-backend

Generic REST backend for mongo written in PHP using the [klein.php](https://github.com/chriso/klein.php) router and the default mongo client.

## Routes

Right now I just have the default 4 REST routes for /[:type] where type is the name of the mongo collection to access.

There's also 2 routes POST /put/[:type]/[:id] and POST /delete/[:type]/[:id] in case PUT and DELETE are not available.

## The Future

The plan is to add a way to query using GET /[:type] and a simple UI to test the submiting queries.
