<entity-list>
    <a class="btn btn-default" href="/"><< Back</a>
    <button class="btn btn-primary" onclick="{ showNewEntityModal }">
        Create new { App.entity_name }
    </button>
    <button class="btn btn-primary desc" onclick="{ sortEntities }">
        Sort { App.entity_name }
    </button>

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
                <button class="btn btn-success btn-view-user" onclick="{ showUpdateEntityModal }">View/Update</button>
                <button class="btn btn-danger btn-delete-user" onclick="{ deleteEntity }">Delete</button>
            </td>
        </tr>
        </tbody>
    </table>

    <script>
        var App = opts.App;

        this.showNewEntityModal = function ()
        {
            $('#newEntityModal').modal('show');
        };

        this.deleteEntity = function (e)
        {
            var $tr = $(e.target).closest('tr');

            App.deleteEntity($tr, App.entities);
        };

        this.sortEntities = function (e)
        {
            var $btn = $(e.target);
            var desc = $btn.hasClass('desc');

            $btn.toggleClass('desc');

            App.entities.comparator = function (entityA, entityB)
            {
                var a = entityA.attributes._id;
                var b = entityB.attributes._id;

                if (a !== b)
                {
                    if (a > b || a === void 0)
                    {
                        return desc ? 1 : -1;
                    }
                    if (a < b || b === void 0)
                    {
                        return desc ? -1 : 1;
                    }
                }

                return 0;
            };
            App.entities.sort();

            //this will refresh the view
            App.entities.trigger('sync');
        };

        this.showUpdateEntityModal = function (e)
        {
            var $btn = $(e.target);
            var $tr = $btn.closest('tr');
            var $entityDetailsModal = $("#entityDetailsModal");

            var id = $tr.data('id');
            var entity = App.entities.findWhere({'_id': id});

            //set the id for the other buttons
            $entityDetailsModal.data('id', entity.attributes['_id']);

            //Render the title
            var $title = $entityDetailsModal.find('.modal-title');
            var titleTmpl = _.template($title.data('templ'));
            $title.text(titleTmpl(entity.attributes));

            //set the user info
            $entityDetailsModal.find('.user-json').val(JSON.stringify(entity.attributes));

            $entityDetailsModal.modal('show');
        }
    </script>
</entity-list>