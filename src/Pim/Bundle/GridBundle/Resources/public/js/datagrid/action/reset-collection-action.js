/* global define */
define(['oro/grid/abstract-action'],
function(AbstractAction) {
    'use strict';

    /**
     * Resets collection to initial state (corrects buggy ORO implementation)
     *
     * @export  oro/grid/reset-collection-action
     * @class   oro.grid.ResetCollectionAction
     * @extends oro.grid.AbstractAction
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
            this.collection.initialState.filters = {};
            // this.collection.initialState.treeId = this.collection.state.treeId;
            // this.collection.initialState.categoryId = this.collection.state.categoryId;
            this.collection.updateState(this.collection.initialState);
            this.collection.fetch();
        }
    });
});
