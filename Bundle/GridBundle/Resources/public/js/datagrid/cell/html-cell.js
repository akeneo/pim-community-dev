var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};

/**
 * Html column cell. Added missing behaviour.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   Oro.Datagrid.Cell.HtmlCell
 * @extends Oro.Datagrid.Cell.StringCell
 */
Oro.Datagrid.Cell.HtmlCell = Oro.Datagrid.Cell.StringCell.extend({
    /**
     * Render a text string in a table cell. The text is converted from the
     * model's raw value for this cell's column.
     */
    render: function () {
        this.$el.empty().html(this.formatter.fromRaw(this.model.get(this.column.get("name"))));
        return this;
    }
});
