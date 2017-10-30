define(['oro/datagrid/delete-action'],
    function(DeleteAction) {
        return DeleteAction.extend({
            /**
             * {@inheritdoc}
             */
            initialize() {
                this.launcherOptions.enabled = this.isEnabled();

                return DeleteAction.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            isEnabled() {
                return false === (this.model.get('document_type') === 'product_model');
            }
        });
    }
);
