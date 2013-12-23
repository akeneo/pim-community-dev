/* global define */
define(['oro/mediator', 'oro/grid/column-form-listener'],
function(mediator, ColumnFormListener) {
    'use strict';

    /**
     * Override oro column form listener to allow dynamic changing of field selectors via the mediator
     *
     * @export  pim/datagrid/column-form-listener
     * @class   pim.datagrid.ColumnFormListener
     * @extends oro.datagrid.ColumnFormListener
     */
    return ColumnFormListener.extend({

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function(options) {
            ColumnFormListener.prototype.initialize.apply(this, arguments);

            mediator.bind('column_form_listener:set_selectors', function(datagridName, selectors) {
                if (datagridName !== this.datagrid.name) {
                    return;
                }
                this._clearState();
                this.selectors = selectors;
                this._restoreState();
                this._synchronizeState();
            }, this);
        }
    });
});
