define(['oro/datagrid/delete-action'],
    function(DeleteAction) {
        return DeleteAction.extend({
            initialize() {
                if (this.model.get('document_type') === 'product_model') {
                    this.launcherOptions.className = 'AknButtonList-item--hide';
                }

                return DeleteAction.prototype.initialize.apply(this, arguments);
            }
        });
    }
);
