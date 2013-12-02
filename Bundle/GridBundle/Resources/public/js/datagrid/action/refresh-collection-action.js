/* global define */
define(['oro/grid/abstract-action'],
function(AbstractAction) {
    'use strict';

    /**
     * Refreshes collection
     *
     * @export  oro/grid/refresh-collection-action
     * @class   oro.grid.RefreshCollectionAction
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
         * Execute refresh collection
         */
        execute: function() {
            this.datagrid.setAdditionalParameter('refresh', true);
            this.collection.fetch();
            this.datagrid.removeAdditionalParameter('refresh');
        }
    });
});
