<entity-list>
    <button id="btnShowNewEntityModal" class="btn btn-primary" onclick="{ showNewEntityModal }">Create new {
        opts.entity_name }
    </button>
    <button id="btnSortEntities" class="btn btn-primary desc">Sort { opts.entity_name }</button>

    <table id="userList" class="table table-striped">
        <thead>
        <tr>
            <td>ID</td>
            <td>Actions</td>
        </tr>
        </thead>
        <tbody>
        <tr each="{ App.entities.models }" data-id="{ attributes._id }">
            <td>{ attributes._id }</td>
            <td>
                <button class="btn btn-primary btn-view-user">View/Update</button>
                <button class="btn btn-danger btn-delete-user" onclick="{ deleteUser }">Delete</button>
            </td>
        </tr>
        </tbody>
    </table>

    <script>
        var App = opts.App;

        this.showNewEntityModal = function ()
        {
            $('#newUserModal').modal('show');
        };

        this.deleteUser = function (e)
        {
            var $btn = $(e.target);
            var $tr = $btn.closest('tr');

            App.deleteUser($tr);
        };
    </script>
</entity-list>