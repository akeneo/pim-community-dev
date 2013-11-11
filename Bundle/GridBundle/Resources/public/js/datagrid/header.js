/* global define */
define(['backbone', 'backgrid', 'oro/grid/header-cell'],
function (Backbone, Backgrid, HeaderCell) {
    "use strict";

    /**
     * Datagrid header widget
     *
     * @export  oro/grid/header
     * @class   oro.grid.Header
     * @extends Backgrid.Header
     */
    return Backgrid.Header.extend({
        /** @property */
        tagName: "thead",

        /** @property */
        row: Backgrid.HeaderRow,

        /** @property */
        headerCell: HeaderCell,

        /**
         * @inheritDoc
         */
        initialize: function (options) {
            if (!options.collection) {
                throw new TypeError("'collection' is required");
            }
            if (!options.columns) {
                throw new TypeError("'columns' is required");
            }

            this.columns = options.columns;
            if (!(this.columns instanceof Backbone.Collection)) {
                this.columns = new Backgrid.Columns(this.columns);
            }

            this.row = new this.row({
                columns: this.columns,
                collection: this.collection,
                headerCell: this.headerCell
            });
        }
    });
});
