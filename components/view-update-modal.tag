<view-update-modal>
    <div id="entityDetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" data-templ="Entity <%= _id %> Details"></h4>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="form-group">
                            <label>
                                You can modify this JSON and resubmit it:
                                <textarea class="user-json form-control" cols="60" rows="10"></textarea>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="{ deleteEntity }">Delete
                        User
                    </button>
                    <button type="button" class="btn btn-primary" onclick="{ updateEntity }">Update User</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <script>
        this.deleteEntity = function ()
        {
            var $modal = $(this.entityDetailsModal);
            var id = $modal.data('id');
            var $tr = $('#userList').find('tr[data-id="' + id + '"]');

            App.deleteEntity($tr, App.entities);
        };

        this.updateEntity = function ()
        {
            var $modal = $(this.entityDetailsModal);
            var id = $modal.data('id');
            var entity = App.entities.findWhere({'_id': id});
            var tag = this;

            if (entity)
            {
                entity.attributes = JSON.parse($modal.find('.user-json').val());
                entity.save(null, {
                    success: function ()
                    {
                        App.entities.add([entity]);

                        $modal.modal('hide');
                    },
                    error: function (model, jqXHR)
                    {
                        var $modalBody = $modal.find('.modal-body');
                        var alert = riot.mount('alert');

                        alert.length && alert[0].unmount();
                        $modalBody.prepend('<alert>');

                        riot.mount('alert', {
                            "message": jqXHR.responseText,
                            "type": "danger"
                        });
                    }
                });
            }
        }
    </script>
</view-update-modal>