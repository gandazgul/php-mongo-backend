var App = App || {};

(function ($)
{
    //Model to handle collections
    App.CollectionModel = Backbone.Model.extend({
        idAttribute: "_id"
    });

    //Backbone Collection to store DB collections
    var Collections = Backbone.Collection.extend({
        url: App.backendUrl + '/collections',
        model: App.CollectionModel
    });

    App.collections = new Collections();

    var collectionList = riot.mount('collection-list', {App: App});
    collectionList = collectionList && collectionList[0];

    App.collections.on('sync remove', function ()
    {
        collectionList.update();
    });

    App.collections.fetch({
        error: App.standardAjaxError
    });
}(jQuery));