<new-entity-modal>
    <div id="newEntityModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create New Entity</h4>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="form-group">
                            <label>
                                Enter the entity's JSON:
                                <textarea class="entity-json form-control" cols="60" rows="10"
                                          placeholder="e.g. \{&quot;first_name&quot;: &quot;John&quot;\}"
                                >\{"key": "value"\}</textarea>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="{ createEntity }">
                        Create Entity
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <script>
        var App = this.opts.App;

        this.createEntity = function ()
        {
            var $modal = $(this.newEntityModal);
            var entity = new App.EntityModel(JSON.parse($modal.find('.entity-json').val()), {
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
</new-entity-modal>