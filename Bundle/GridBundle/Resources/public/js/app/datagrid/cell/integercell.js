var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};

/**
 * Integer column cell. Added missing behaviour.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   Oro.Datagrid.Cell.IntegerCell
 * @extends Backgrid.IntegerCell
 */
Oro.Datagrid.Cell.IntegerCell = Backgrid.IntegerCell.extend({
    /**
     * @inheritDoc
     */
    enterEditMode: function (e) {
        if (this.column.get("editable")) {
            e.stopPropagation();
        }
        return Backgrid.IntegerCell.prototype.enterEditMode.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    exitEditMode: function (e) {
        if (this.column.get("editable")) {
            this.trigger("edited", this);
        }
        return Backgrid.IntegerCell.prototype.exitEditMode.apply(this, arguments);
    }
});
