var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Datagrid header widget
 *
 * @class   Oro.Datagrid.Header
 * @extends Backgrid.Header
 */
Oro.Datagrid.Header = Backgrid.Header.extend({
    /** @property */
    tagName: "thead",

    /** @property */
    row: Backgrid.HeaderRow,

    /** @property */
    headerCell: Oro.Datagrid.HeaderCell,

    /**
     * @inheritDoc
     */
    initialize: function (options) {
        if (!options.collection) {
            throw new TypeError("'collection' is required")
        }
        if (!options.columns) {
            throw new TypeError("'columns' is required")
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
