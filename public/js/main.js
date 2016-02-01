App = window.App || {};

(function ($)
{
    App.backendUrl = 'http://backend.local/';

    var User = Backbone.Model.extend({
        idAttribute: "_id"
    });

    var Users = Backbone.Collection.extend({
        url: App.backendUrl + 'users',
        model: User
    });

    var users = new Users;

    var UserListView = Backbone.View.extend({
        el: '#userList tbody',
        template: _.template($('#userRowTempl').html()),
        collection: users,
        render: function ()
        {
            var html = '';
            var view = this;

            this.collection.models.forEach(function (user)
            {
                var defaultModel = {
                    first_name: "",
                    last_name: ""
                };
                var model = $.extend({}, defaultModel, user.attributes);

                html += view.template(model);
            });

            this.$el.html(html);
        }
    });

    var userListView = new UserListView();

    $('#btnShowUserModal').on('click', function ()
    {
        $("#newUserModal").modal('show');
    });

    $('#btnCreateUser').on('click', function ()
    {
        var user = new User({
            "first_name": "new test",
            "last_name": "new test last",
            "title": "some title",
            "age": 25
        }, {
            "collection": users
        });

        user.save(null, {
            success: function ()
            {
                users.add([user]);

                $("#newUserModal").modal('hide');
            }
        });
    });

    $('#userList').on('click', '.btn-delete-user', function ()
    {
        var $btn = $(this);
        var $tr = $btn.closest('tr');
        var id = $tr.data('id');

        var user = users.where({'_id': id});

        if (user)
        {
            $tr.hide();

            user[0].destroy({
                success: function ()
                {
                    users.remove(user);
                    $tr.remove();
                },
                error: function ()
                {
                    $tr.show();
                }
            });
        }
    });

    $(document).ready(function ()
    {
        users.on('sync', function ()
        {
            userListView.render();
        });
        users.fetch();
    });
}(jQuery));