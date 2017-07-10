/* global define */
import Backbone from 'backbone';
import Backgrid from 'backgrid';
import HeaderCell from 'oro/datagrid/header-cell';
    

    /**
     * Datagrid header widget
     *
     * @export  oro/datagrid/header
     * @class   oro.datagrid.Header
     * @extends Backgrid.Header
     */
    export default Backgrid.Header.extend({
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

