<collection-list>

    <h1>Existing Collections</h1>

    <ul>
        <li each="{ name in collections }">
            <a href="/{ name }">{ name }</a>
        </li>
    </ul>

    <button class="btn btn-success" onclick="{ createNewCollection }">Create new collection</button>

    <script>
        this.collections = opts.collections;
        var App = opts.App;

        this.createNewCollection = function ()
        {
            var collection_name = window.prompt("Enter a collection name:");
            var tag = this;

            if (collection_name)
            {
                $.ajax({
                    url: '/collections',
                    method: 'POST',
                    data: {name: collection_name},
                    dataType: "json",
                    success: function ()
                    {
                        tag.collections.push(collection_name);
                        tag.update();
                    },
                    error: App.standardAjaxError
                });
            }
        }
    </script>
</collection-list>