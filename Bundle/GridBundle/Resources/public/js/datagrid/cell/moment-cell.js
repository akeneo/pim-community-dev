/* global define */
define(['underscore', 'backgrid', 'oro/datagrid/moment-formatter', 'backgrid/moment'],
function(_, Backgrid, MomentFormatter) {
    'use strict';

    /**
     * Datetime column cell. Added missing behavior.
     *
     * Triggers events:
     *  - "edit" when a cell is entering edit mode and an editor
     *  - "editing" when a cell has finished switching to edit mode
     *  - "edited" when cell editing is finished
     *
     * @export  oro/datagrid/moment-cell
     * @class   oro.datagrid.MomentCell
     * @extends Backgrid.Extension.MomentCell
     */
    return Backgrid.Extension.MomentCell.extend({

        /** @property {Backgrid.CellFormatter} */
        formatter: MomentFormatter,

        /**
         * @inheritDoc
         */
        enterEditMode: function (e) {
            if (this.column.get("editable")) {
                e.stopPropagation();
            }
            return Backgrid.Extension.MomentCell.prototype.enterEditMode.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        exitEditMode: function (e) {
            if (this.column.get("editable")) {
                this.trigger("edited", this);
            }
            return Backgrid.Extension.MomentCell.prototype.exitEditMode.apply(this, arguments);
        }
    });
});
