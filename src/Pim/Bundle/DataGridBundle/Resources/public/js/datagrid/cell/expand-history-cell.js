/* global define */
define(['underscore', 'backgrid', 'pim/template/datagrid/cell/expand-history-cell'],
    function(_, Backgrid, template) {
        'use strict';

        /**
         * Expand history cell.
         *
         * @export  oro/datagrid/expand-history-cell
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
