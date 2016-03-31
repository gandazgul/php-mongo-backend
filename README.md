# php-mongo-backend

Generic REST backend for MongoDB written in PHP using the [klein.php](https://github.com/chriso/klein.php) router and the default MongoDB client. Includes a FE based on bootstrap, Backbone.JS models and riot.js components.

## Routes

RESTful resource routes for /[:type] where type is the name of the Mongo collection to access.

There's also 2 routes POST /put/[:type]/[:id] and POST /delete/[:type]/[:id] in case PUT and DELETE are not available.

You can pass a ?where= param to GET /[:type] that will be passed on to MongoDB, you can use almost any of the operators that MongoDB supports.

There's RESTful routes for collections as well (TODO: rename collections)

The UI comes up if you send the http header Accept: text/html (think browsers) otherwise you get a json representation of a collection or a single document. The home page displays the list of collections available provides actions to delete and create new ones. Each collection link /[:type] will bring up a list of all documents in that collection and provides all CRUD actions. Currently working on CRUD at the individual document url (/[:type]/[:id]).

## The Future

I'll be adding other things as I need them or requested. Please open an issues if you want me to add anything or if you found a bug. Pull requests accepted as well.

Full coverage tests coming as well.
