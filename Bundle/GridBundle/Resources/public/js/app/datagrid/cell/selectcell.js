var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};

/**
 * Select column cell. Added missing behaviour.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   Oro.Datagrid.Cell.SelectCell
 * @extends Backgrid.SelectCell
 */
Oro.Datagrid.Cell.SelectCell = Backgrid.SelectCell.extend({
    /**
     * @inheritDoc
     */
    initialize: function (options) {
        if (this.choices) {
            this.optionValues = [];
            _.each(this.choices, function(value, key) {
                this.optionValues.push([value, key]);
            }, this);
        }
        Backgrid.SelectCell.prototype.initialize.apply(this, arguments);
    },

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
