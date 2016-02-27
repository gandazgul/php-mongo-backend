App = window.App || {};

(function ($)
{
    //App init, data =========================================================================
    App.backendUrl = location.protocol + '//' + $('meta[name="base_url"]').prop('content');

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

    /**
     * Deletes a user
     * Called from the details modal and from the delete action on the entity list
     *
     * @param $tr
     * @param callback
     */
    App.deleteUser = function ($tr, callback)
    {
        var answer = window.confirm("Are you sure?");
        if (answer)
        {
            var id = $tr.data('id');
            var entity = App.entities.findWhere({'_id': id});

            if (entity)
            {
                $tr.fadeOut(400, function ()
                {
                    entity.destroy({
                        success: function ()
                        {
                            App.entities.remove(entity);
                            $tr.remove();

                            if (callback)
                            {
                                callback();
                            }
                        },
                        error: function ()
                        {
                            $tr.show();
                        }
                    });
                });
            }
        }
    };

    riot.mount('new-user-modal', {App: App});
    var entityList = riot.mount('entity-list', {App: App});
    entityList = entityList && entityList[0];

    App.entities.on('sync', function ()
    {
        entityList.update();
    });

    $(document).ready(function ()
    {
        App.entities.fetch();
    })

}(jQuery));