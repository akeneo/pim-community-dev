/* global define */
define(['underscore', 'backgrid'],
    function(_, Backgrid) {
        'use strict';

        /**
         * Integer column cell.
         *
         * @export  oro/datagrid/integer-cell
         * @class   oro.datagrid.NumberCell
         * @extends Backgrid.NumberCell
         */
        return Backgrid.NumberCell.extend({
            /** @property {String} */
            style: 'decimal',

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.decimals = 0;

                Backgrid.NumberCell.prototype.initialize.apply(this, arguments);
            },

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
