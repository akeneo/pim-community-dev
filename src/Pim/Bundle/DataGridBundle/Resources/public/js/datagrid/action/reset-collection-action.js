/* global define */
define(['underscore', 'oro/datagrid/abstract-action'],
function(_, AbstractAction) {
    'use strict';

    /**
     * Resets collection to initial state
     *
     * @export  oro/datagrid/reset-collection-action
     * @class   oro.datagrid.ResetCollectionAction
     * @extends oro.datagrid.AbstractAction
     */
    return AbstractAction.extend({

        /** @property oro.PageableCollection */
        collection: undefined,

        /**
         * Initialize action
         *
         * @param {Object} options
         * @param {oro.PageableCollection} options.collection Collection
         * @throws {TypeError} If collection is undefined
         */
        initialize: function(options) {
            options = options || {};

            if (!options.datagrid) {
                throw new TypeError("'datagrid' is required");
            }
            this.collection = options.datagrid.collection;

            AbstractAction.prototype.initialize.apply(this, arguments);
        },

        /**
         * Execute reset collection
         */
        execute: function() {
            var initialState = this.collection._initState;

            if (_.has(initialState, 'filters')) {
                initialState.filters = _.omit(initialState.filters, 'scope');
            }

            this.collection.updateState(initialState);
            this.collection.fetch();
        }
    });
});
