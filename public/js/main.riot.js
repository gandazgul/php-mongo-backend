App = window.App || {};

(function ($)
{
    //App init, data =========================================================================
    App.backendUrl = '//' + $('meta[name="base_url"]').prop('content');

    var path_parts = location.pathname.split('/');
    var entity_name = (path_parts && path_parts[1]) || 'users';

    var EntityModel = Backbone.Model.extend({
        idAttribute: "_id"
    });

    var Entities = Backbone.Collection.extend({
        url: App.backendUrl + '/' + entity_name,
        model: EntityModel
    });

    var entities = new Entities();

    riot.mount('new-user-modal', {entities: entities, EntityModel: EntityModel});
    var entityList = riot.mount('entity-list', {entity_name: entity_name, entities: entities});
    entityList = entityList && entityList[0];

    entities.on('sync', function ()
    {
        entityList.update();
    });

    $(document).ready(function ()
    {
        entities.fetch();
    })

}(jQuery));