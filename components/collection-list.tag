<collection-list>

    <h1>Existing Collections</h1>

    <table id="userList" class="table table-striped">
        <thead>
        <tr>
            <td>Name</td>
            <td>Actions</td>
        </tr>
        </thead>
        <tbody>
        <tr each="{ App.collections.models }" data-id="{ attributes._id }">
            <td>
                <a href="/{ attributes.name }">
                    { attributes.name }
                </a>
            </td>
            <td>
                <button class="btn btn-danger" onclick="{ deleteCollection }">Delete</button>
            </td>
        </tr>
        </tbody>
    </table>

    <button class="btn btn-success" onclick="{ createNewCollection }">Create new collection</button>

    <script>
        var App = opts.App;

        this.createNewCollection = function ()
        {
            var collection_name = window.prompt("Enter a collection name:");

            if (collection_name)
            {
                var collection = new App.CollectionModel({
                    'name': collection_name
                }, {
                    "collection": App.collections
                });

                collection.save({
                    success: function ()
                    {
                        App.collections.add([collection_name]);
                    },
                    error: App.standardAjaxError
                });
            }
        };

        this.deleteCollection = function (e)
        {
            var $tr = $(e.target).closest('tr');

            App.deleteEntity($tr, App.collections);
        }
    </script>
</collection-list>