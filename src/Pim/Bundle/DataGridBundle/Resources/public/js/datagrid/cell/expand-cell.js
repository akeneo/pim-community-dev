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
        return Backgrid.StringCell.extend({
            /** @property */
            className: "AknGrid-bodyCell AknGrid-expandable",

            /**
             * Render the completeness.
             */
            render: function () {
                this.$el.empty().html(
                    '<span class="AknButtonList"><div class="AknButtonList-item version-expander AknGrid-expand"></div><span class="AknButtonList-item AknButton AknButton--grey AknButton--round">' + this.model.get(this.column.get('name')) + '</span></span>'
                );

                return this;
            }
        });
    });
