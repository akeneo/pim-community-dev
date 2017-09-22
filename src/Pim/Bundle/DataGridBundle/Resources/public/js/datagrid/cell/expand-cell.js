/* global define */
define(['underscore', 'backgrid', 'pim/template/datagrid/cell/history-diff-cell'],
    function(_, Backgrid, template) {
        'use strict';

        /**
         * Number column cell.
         *
         * @export  oro/datagrid/expand-cell
         * @class   oro.datagrid.ExpandCell
         * @extends Backgrid.StringCell
         */
        return Backgrid.StringCell.extend({
            /** @property */
            className: 'AknGrid-bodyCell AknGrid-expandable',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().html(this.template({
                    value: this.model.get(this.column.get('name'))
                }));

                return this;
            }
        });
    });
