var App = window.App || {};

(function ($)
{
    var path_parts = location.pathname.split('/');
    App.entity_name = (path_parts && path_parts[1]) || 'users';

    App.EntityModel = Backbone.Model.extend({
        idAttribute: "_id"
    });

    var Entities = Backbone.Collection.extend({
        url: App.backendUrl + '/' + App.entity_name,
        model: App.EntityModel
    });

    App.entities = new Entities();

    riot.mount('new-entity-modal', {App: App});
    riot.mount('view-update-modal', {App: App});
    var entityList = riot.mount('entity-list', {App: App});
    entityList = entityList && entityList[0];

    App.entities.on('sync remove', function ()
    {
        entityList.update();
    });

    $(document).ready(function ()
    {
        App.entities.fetch();
    });
}(jQuery));