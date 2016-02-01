(function ($) {
    $('#btnGetUsers').on('click', function () {
        var User = Backbone.Model.extend({});
        var Users = Backbone.Collection.extend({
            url: 'http://backend.local/users',
            model: User
        });

        var users = new Users;

        var UserListView = Backbone.View.extend({
            'el': '#userList',
            collection: users,
            render: function () {
                var html = '';

                this.collection.models.forEach(function (user) {
                    var $li = $('<li />').addClass('user').text(user.get('first_name') + ' ' + user.get('last_name'));

                    html += $('<div />').append($li).html();
                });

                this.$el.html(html);
            }
        });

        var userListView = new UserListView();

        users.on('sync', function () {
            userListView.render();
        });
        users.fetch();

    });
}(jQuery));