var App = App || {};

(function ($)
{
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

    $.ajax({
        url: '/collections',
        dataType: 'json',
        success: function (collections)
        {
            riot.mount('collection-list', {App: App, collections: collections});
        },
        error: App.standardAjaxError
    });
}(jQuery));