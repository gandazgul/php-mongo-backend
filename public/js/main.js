App = window.App || {};

(function ($)
{
    //App init
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

    //create user
    $('#btnShowUserModal').on('click', function ()
    {
        $("#newUserModal").modal('show');
    });

    $('#btnCreateUser').on('click', function ()
    {
        var user = new User(JSON.parse($('#newUserModal').find('.user-json').val()), {
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

    //Update and delete
    var $userList = $('#userList');

    function deleteUserClick($tr, callback)
    {
        var answer = window.confirm("Are you sure?");
        if (answer)
        {
            var id = $tr.data('id');
            var user = users.findWhere({'_id': id});

            if (user)
            {
                $tr.hide();

                user.destroy({
                    success: function ()
                    {
                        users.remove(user);
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
            }
        }
    }

    $userList.on('click', '.btn-delete-user', function ()
    {
        var $btn = $(this);
        var $tr = $btn.closest('tr');

        deleteUserClick($tr);
    });

    var $userDetailsModal = $('#userDetailsModal');

    $userDetailsModal.on('show.bs.modal', function ()
    {
        var $modal = $(this);

        $modal.find('.btn-delete-user').on('click', function ()
        {
            var id = $userDetailsModal.data('id');
            var $tr = $userList.find('tr[data-id="' + id + '"]');

            deleteUserClick($tr, function ()
            {
                $modal.modal('hide');
            });
        });

        $modal.find('.btn-update-user').on('click', function ()
        {
            var id = $userDetailsModal.data('id');

            var user = users.findWhere({'_id': id});

            if (user)
            {
                user.attributes = JSON.parse($userDetailsModal.find('.user-json').val());
                user.save(null, {
                    success: function ()
                    {
                        users.add([user]);
                        userListView.render();

                        $modal.modal('hide');
                    },
                    error: function (model, jqXHR)
                    {
                        var templ = _.template($('#errorTempl').html());
                        var $modalBody = $modal.find('.modal-body');

                        $modalBody.find('.alert').remove();

                        $modalBody.prepend(templ({"message": jqXHR.responseText}));
                    }
                });
            }
        });
    });

    $userList.on('click', '.btn-view-user', function ()
    {
        var $btn = $(this);
        var $tr = $btn.closest('tr');
        var id = $tr.data('id');

        var user = users.findWhere({'_id': id});

        if (user)
        {
            //set the id for the other buttons
            $userDetailsModal.data('id', id);

            //Render the title
            var $title = $userDetailsModal.find('.modal-title');
            var titleTmpl = _.template($title.data('templ'));
            $title.text(titleTmpl(user.attributes));

            //set the user info
            $userDetailsModal.find('.user-json').val(JSON.stringify(user.attributes));

            $userDetailsModal.modal('show');
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