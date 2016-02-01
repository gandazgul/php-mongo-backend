App = window.App || {};

(function ($)
{
    //App init, data =========================================================================
    App.backendUrl = 'http://backend.local/';

    var User = Backbone.Model.extend({
        idAttribute: "_id"
    });

    var Users = Backbone.Collection.extend({
        url: App.backendUrl + 'users',
        model: User
    });

    var users = new Users;

    // Common functions =======================================================================
    /**
     * Deletes a user
     * Called from the details modal and from the delete action on the users list
     *
     * @param $tr
     * @param callback
     */
    function deleteUser($tr, callback)
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

    // Views ========================================================================================
    /**
     * User details modal, user update and another delete button
     */
    var UserDetailModalView = Backbone.View.extend({
        el: '#userDetailsModal',
        events: {
            "click .btn-update-user": "updateUser",
            "click .btn-delete-user": "deleteUser"
        },
        initialize: function (user)
        {
            this.model = user;
        },
        render: function ()
        {
            //set the id for the other buttons
            var attributes = this.model.attributes;
            this.$el.data('id', attributes['_id']);

            //Render the title
            var $title = this.$el.find('.modal-title');
            var titleTmpl = _.template($title.data('templ'));
            $title.text(titleTmpl(attributes));

            //set the user info
            this.$el.find('.user-json').val(JSON.stringify(attributes));

            this.$el.modal('show');
        },
        deleteUser: function ()
        {
            var id = this.model.attributes['_id'];
            var $tr = $('#userList').find('tr[data-id="' + id + '"]');
            var view = this;

            deleteUser($tr, function ()
            {
                view.$el.modal('hide');
            });
        },
        updateUser: function ()
        {
            var user = this.model;
            var view = this;

            if (user)
            {
                user.attributes = JSON.parse(this.$el.find('.user-json').val());
                user.save(null, {
                    success: function ()
                    {
                        users.add([user]);
                        userListView.render();

                        view.$el.modal('hide');
                    },
                    error: function (model, jqXHR)
                    {
                        var errorTempl = _.template($('#errorTempl').html());
                        var $modalBody = view.$el.find('.modal-body');

                        $modalBody.find('.alert').remove();

                        $modalBody.prepend(errorTempl({"message": jqXHR.responseText}));
                    }
                });
            }
        }
    });

    /**
     * User table with action buttons
     */
    var UserListView = Backbone.View.extend({
        el: '#userList tbody',
        events: {
            "click .btn-view-user": "viewUser",
            "click .btn-delete-user": "deleteUser"
        },
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
        },
        viewUser: function (e)
        {
            var $btn = $(e.target);
            var $tr = $btn.closest('tr');
            var id = $tr.data('id');

            var user = users.findWhere({'_id': id});

            if (user)
            {
                var modalView = new UserDetailModalView(user);
                modalView.render();
            }
        },
        deleteUser: function (e)
        {
            var $btn = $(e.target);
            var $tr = $btn.closest('tr');

            deleteUser($tr);
        }
    });
    var userListView = new UserListView();

    var CreateUserModalView = Backbone.View.extend({
        el: '#newUserModal',
        events: {
            'click #btnCreateUser': 'createUser'
        },
        render: function ()
        {
            this.$el.modal('show');
        },
        createUser: function ()
        {
            var $modal = this.$el;
            var user = new User(JSON.parse($modal.find('.user-json').val()), {
                "collection": users
            });

            user.save(null, {
                success: function ()
                {
                    users.add([user]);

                    $modal.modal('hide');
                }
            });
        }
    });

    /**
     * Whole App view
     */
    var AppView = Backbone.View.extend({
        el: 'body',
        model: App,
        events: {
            "click #btnShowUserModal": 'showUserModal'
        },
        render: function ()
        {
            users.on('sync', function ()
            {
                userListView.render();
            });

            users.fetch();
        },
        showUserModal: function ()
        {
            var createUserModalView = new CreateUserModalView();
            createUserModalView.render();
        }
    });

    $(document).ready(function ()
    {
        var appView = new AppView();
        appView.render();
    });
}(jQuery));