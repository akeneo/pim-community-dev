/* global define */
define(['backgrid', 'oro/datagrid/cell-formatter'],
function(Backgrid, CellFormatter) {
    'use strict';

    /**
     * Textarea column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/textarea-cell
     * @class   oro.datagrid.TextareaCell
     * @extends Backgrid.TextareaCell
     */
    return Backgrid.TextareaCell.extend({
        /**
         @property {(Backgrid.CellFormatter|Object|string)}
         */
        formatter: new CellFormatter(),

        textareaTooltip: function () {
            this.$('span.cell-truncatable').popover({delay: {show: 500, hide: 100},trigger: 'hover'});
        }
    });
});
