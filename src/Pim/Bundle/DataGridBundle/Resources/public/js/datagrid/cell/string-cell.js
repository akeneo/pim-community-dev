/* global define */
define(['backgrid', 'oro/datagrid/cell-formatter'],
function(Backgrid, CellFormatter) {
    'use strict';
    
    /**
     * String column cell. Added missing behaviour.
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
        }
    });
});
