define(
    ['oro/datagrid/abstract-action'],
    function(AbstractAction) {
        'use strict';

        /**
         * Override abstract action to add support for export action
         */
        return AbstractAction.extend({
            executeConfiguredAction: function(action) {
                if (action.frontend_type == 'export') {
                    this._handleExport(action);
                } else {
                    AbstractAction.prototype.executeConfiguredAction.apply(this, arguments);
                }
            },

            _handleExport: function(action) {
                if (action.dispatched) {
                    return;
                }
                require(
                    ['oro/' + action.frontend_type + '-widget'],
                    function(ExportAction) {
                        var exportAction = new ExportAction(action);
                        exportAction.run();
                    }
                );
            }
        });
    }
);
