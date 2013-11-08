/* global define */
define(['underscore', 'backgrid', 'oro/grid/number-formatter'],
function(_, Backgrid, NumberFormatter) {
    'use strict';

    /**
     * Number column cell.
     *
     * @export  oro/grid/number-cell
     * @class   oro.grid.NumberCell
     * @extends Backgrid.NumberCell
     */
    return Backgrid.NumberCell.extend({
        /** @property {oro.datagrid.NumberFormatter} */
        formatterPrototype: NumberFormatter,

        /** @property {String} */
        style: 'decimal',

        /**
         * @inheritDoc
         */
        initialize: function (options) {
            _.extend(this, options);
            Backgrid.Cell.prototype.initialize.apply(this, arguments);
            this.formatter = this.createFormatter();
        },

        /**
         * Creates number cell formatter
         *
         * @return {oro.datagrid.NumberFormatter}
         */
        createFormatter: function() {
            return new this.formatterPrototype({style: this.style});
        },

        /**
         * @inheritDoc
         */
        enterEditMode: function (e) {
            if (this.column.get("editable")) {
                e.stopPropagation();
            }
            return Backgrid.NumberCell.prototype.enterEditMode.apply(this, arguments);
        }
    });
});
