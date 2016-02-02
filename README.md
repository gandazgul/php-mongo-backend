# php-mongo-backend

Generic REST backend for MongoDB written in PHP using the [klein.php](https://github.com/chriso/klein.php) router and the default MongoDB client. Includes a FE based on bootstrap and Backbone.JS models and views.

## Routes

Right now I just have the default 4 REST routes for /[:type] where type is the name of the mongo collection to access.

There's also 2 routes POST /put/[:type]/[:id] and POST /delete/[:type]/[:id] in case PUT and DELETE are not available.

You can pass a ?where= param to GET /[:type] that will be passed on to MongoDB, you can use almost any of the operators that MongoDB supports.

## The Future

I'll be adding other things as I need them or requested.
