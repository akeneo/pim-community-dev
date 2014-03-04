/* global define */
define(['oro/datagrid/mass-action'],
function(MassAction) {
    'use strict';

    /**
     * Override abstract action to add the datalocale parameter
     */
    return MassAction.extend({
        /**
         * Get action parameters
         *
         * @returns {Object}
         * @private
         */
        getActionParameters: function() {
            var selectionState = this.datagrid.getSelectionState();
            var collection = this.datagrid.collection;
            var idValues = _.map(selectionState.selectedModels, function(model) {
                return model.get(this.identifierFieldName)
            }, this);

            var params = {
                inset: selectionState.inset ? 1 : 0,
                values: idValues.join(',')
            };

            params = collection.processFiltersParams(params, null, 'filters');

            var locale = decodeURIComponent(this.datagrid.collection.url).split('dataLocale]=').pop();
            if (locale) {
                params['dataLocale']= locale;
            }

            return params;
        }

    });
});
