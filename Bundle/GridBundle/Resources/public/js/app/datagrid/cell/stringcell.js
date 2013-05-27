var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};

/**
 * String column cell. Added missing behaviour.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   Oro.Datagrid.Cell.StringCell
 * @extends Backgrid.StringCell
 */
Oro.Datagrid.Cell.StringCell = Backgrid.StringCell.extend({
    /**
     @property {Backgrid.CellFormatter|Object|string}
     */
    formatter: new Oro.Datagrid.Cell.Formatter.CellFormatter(),

    /**
     * @inheritDoc
     */
    enterEditMode: function (e) {
        if (this.column.get("editable")) {
            e.stopPropagation();
        }
        return Backgrid.StringCell.prototype.enterEditMode.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    exitEditMode: function (e) {
        if (this.column.get("editable")) {
            this.trigger("edited", this);
        }
        return Backgrid.StringCell.prototype.exitEditMode.apply(this, arguments);
    }
});
