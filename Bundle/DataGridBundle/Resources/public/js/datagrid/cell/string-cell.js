/* global define */
define(['backgrid', 'oro/datagrid/cell-formatter'],
function(Backgrid, CellFormatter) {
    'use strict';

    /**
     * String column cell. Added missing behaviour.
     *
     * Triggers events:
     *  - "edit" when a cell is entering edit mode and an editor
     *  - "editing" when a cell has finished switching to edit mode
     *  - "edited" when cell editing is finished
     *
     * @export  oro/datagrid/string-cell
     * @class   oro.datagrid.StringCell
     * @extends Backgrid.StringCell
     */
    return Backgrid.StringCell.extend({
        /**
         @property {(Backgrid.CellFormatter|Object|string)}
         */
        formatter: new CellFormatter(),

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
});
