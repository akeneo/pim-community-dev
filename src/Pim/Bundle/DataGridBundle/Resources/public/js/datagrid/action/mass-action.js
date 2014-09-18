/* global define */
define(['underscore', 'oro/datagrid/mass-action'],
function(_, MassAction) {
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
            var collection     = this.datagrid.collection;

            var idValues = _.map(selectionState.selectedModels, function(model) {
                return model.get(this.identifierFieldName);
            }, this);
            var params = {
                inset: selectionState.inset ? 1 : 0,
                values: idValues.join(',')
            };

            params = this.getExtraParameters(params, collection.state)
            params = collection.processFiltersParams(params, null, 'filters');

            var locale = decodeURIComponent(this.datagrid.collection.url).split('dataLocale]=').pop();
            if (locale) {
                params.dataLocale = locale;
            }

            return params;
        },
        getExtraParameters: function(params, state) {
            params['product-grid'] = {};

            if (state != undefined) {
                params['product-grid']['_parameters'] = this.getActiveSorters(state);
                params['product-grid']['_sort_by']    = this.getActiveColumns(state);
            }

            return params;
        },
        getActiveSorters: function(state) {
            var result = {};

            if (state.parameters != undefined) {
                result['view'] = {
                    columns: state.parameters.view.columns
                };
            }

            return result;
        },
        getActiveColumns: function(state) {
            var result = {};

            if (state.sorters != undefined) {
                for (var sorterKey in state.sorters) {
                    result[sorterKey] = state.sorters[sorterKey] === 1 ? 'DESC' : 'ASC';
                }
            }

            return result;
        }
    });
});
