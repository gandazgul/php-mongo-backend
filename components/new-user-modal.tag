<new-user-modal>
    <div id="newUserModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">New User Information</h4>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="form-group">
                            <label>
                                Enter the user's JSON:
                                <textarea class="user-json form-control" cols="60" rows="10"
                                          placeholder="e.g. \{&quot;first_name&quot;: &quot;John&quot;\}"
                                >\{
                                        "first_name": "new test",
                                        "last_name": "new test last",
                                        "title": "some title",
                                        "age": 25
                                    \}
                                </textarea>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnCreateUser" onclick="{ createUser }">Create
                        User
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <script>
        var App = this.opts.App;

        this.createUser = function ()
        {
            var $modal = $(this.newUserModal);
            var entity = new App.EntityModel(JSON.parse($modal.find('.user-json').val()), {
                "collection": App.entities
            });

            entity.save(null, {
                success: function ()
                {
                    App.entities.add([entity]);


                    $modal.modal('hide');
                }
            });
        }
    </script>
</new-user-modal>