/* global define */
define(['backgrid'],
function(Backgrid) {
    'use strict';

    /**
     * Number column cell. Added missing behaviour.
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
        }
    });
});
