var App = App || {};

(function ($)
{
    //App init, data =========================================================================
    App.backendUrl = location.protocol + '//' + $('meta[name="base_url"]').prop('content');

    /**
     * Standard AJAX error handler to display an alert
     * @param {{}} jqXHR
     */
    App.standardAjaxError = function (jqXHR)
    {
        var $body = $('body');

        //remove previous alerts
        $body.find('alert').remove();
        $body.prepend('<alert />');

        riot.mount('alert', {
            'type': 'danger',
            'message': jqXHR.responseText
        });
    };

    /**
     * Deletes an entity
     * Called from the details modal and from the delete action on the entity list
     *
     * @param {jQuery} $entityElem
     * @param {Backbone.Collection} collection
     * @param {function} [callback]
     */
    App.deleteEntity = function ($entityElem, collection, callback)
    {
        var answer = window.confirm("Are you sure?");
        if (answer)
        {
            var id = $entityElem.data('id');
            var entity = collection.findWhere({'_id': id});

            if (entity)
            {
                $entityElem.fadeOut(400, function ()
                {
                    entity.destroy({
                        success: function ()
                        {
                            collection.remove(entity);

                            if (callback)
                            {
                                callback();
                            }
                        },
                        error: function (jqXHR)
                        {
                            $entityElem.show();

                            App.standardAjaxError(jqXHR);
                        }
                    });
                });
            }
        }
    };
}(jQuery));
