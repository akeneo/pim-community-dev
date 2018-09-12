/* global define */
define(['underscore', 'backgrid'],
    function(_, Backgrid) {
        'use strict';

        /**
         * Number column cell.
         *
         * @export  oro/datagrid/number-cell
         * @class   oro.datagrid.NumberCell
         * @extends Backgrid.NumberCell
         */
        return Backgrid.NumberCell.extend({
            /** @property {String} */
            style: 'decimal',

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
