/* global define */
define(['backgrid'],
function(Backgrid) {
    'use strict';

    /**
     * Number column cell. Added missing behaviour.
     *
     * Triggers events:
     *  - "edit" when a cell is entering edit mode and an editor
     *  - "editing" when a cell has finished switching to edit mode
     *  - "edited" when cell editing is finished
     *
     * @export  oro/datagrid/number-cell
     * @class   oro.datagrid.NumberCell
     * @extends Backgrid.NumberCell
     */
    return Backgrid.NumberCell.extend({
        /**
         * @inheritDoc
         */
        enterEditMode: function (e) {
            if (this.column.get("editable")) {
                e.stopPropagation();
            }
            return Backgrid.NumberCell.prototype.enterEditMode.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        exitEditMode: function (e) {
            if (this.column.get("editable")) {
                this.trigger("edited", this);
            }
            return Backgrid.NumberCell.prototype.exitEditMode.apply(this, arguments);
        }
    });
});
