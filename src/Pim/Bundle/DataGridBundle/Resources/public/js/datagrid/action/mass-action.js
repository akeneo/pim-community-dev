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
            var collection = this.datagrid.collection;
            var idValues = _.map(selectionState.selectedModels, function(model) {
                return model.get(this.identifierFieldName);
            }, this);
            var params = {
                inset: selectionState.inset ? 1 : 0,
                values: idValues.join(',')
            };

            if (collection.state != undefined) {
                if (collection.state.parameters != undefined) {
                    params['product-grid'] = {
                        '_parameters': {
                            view: {
                                columns: collection.state.parameters.view.columns
                            }
                        }
                    };
                }

                for (var key in collection.state.sorters) {
                    if (params['product-grid'] == undefined)  {
                        params['product-grid'] = {};
                    }

                    params['product-grid']['_sort_by'] = {};
                    params['product-grid']['_sort_by'][key] = collection.state.sorters[key] === 1 ? 'DESC' : 'ASC';
                }
            }

            params = collection.processFiltersParams(params, null, 'filters');

            var locale = decodeURIComponent(this.datagrid.collection.url).split('dataLocale]=').pop();
            if (locale) {
                params.dataLocale = locale;
            }

            return params;
        },
        getExtraParameters: function
        getActiveSorters: function(state) {
            if (state.parameters != undefined) {
                return {
                    '_parameters': {
                        view: {
                            columns: state.parameters.view.columns
                        }
                    }
                };
            }
        },
        getActiveColumns: function(state) {
            var result = {};

            for (var sorterKey in state.sorters) {
                result.push({'sorterKey': state.sorters[sorterKey] === 1 ? 'DESC' : 'ASC'});
            }

            return result;
        }
    });
});
