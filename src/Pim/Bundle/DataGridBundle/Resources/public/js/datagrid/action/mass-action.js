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

            params = this.getExtraParameters(params, collection.state);
            params = collection.processFiltersParams(params, null, 'filters');

            var locale = decodeURIComponent(this.datagrid.collection.url).split('dataLocale]=').pop();
            if (locale) {
                params.dataLocale = locale;
            }

            return params;
        },

        /**
         * Get extra parameters (sorters and custom parameters)
         * @param {array}  params
         * @param {object} state
         *
         * @return {object}
         */
        getExtraParameters: function(params, state) {
            params[this.datagrid.name] = {};

            if (state !== undefined) {
                params[this.datagrid.name]._parameters = this.getActiveSorters(state);
                params[this.datagrid.name]._sort_by    = this.getActiveColumns(state);
            }

            return params;
        },

        /**
         * Get active sorters
         * @param {object} state
         *
         * @return {object}
         */
        getActiveSorters: function(state) {
            var result = {};

            if (state.parameters !== undefined && state.parameters.view !== undefined) {
                result.view = {
                    columns: state.parameters.view.columns
                };
            }

            return result;
        },

        /**
         * Get active columns
         * @param {object} state
         *
         * @return {object}
         */
        getActiveColumns: function(state) {
            var result = {};

            if (state.sorters !== undefined) {
                for (var sorterKey in state.sorters) {
                    result[sorterKey] = state.sorters[sorterKey] === 1 ? 'DESC' : 'ASC';
                }
            }

            return result;
        }
    });
});
