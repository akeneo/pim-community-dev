/* global define */
define(
    [
        'jquery',
        'underscore',
        'backgrid',
        'oro/datagrid/row',
        'pim/template/datagrid/row/product'
    ],
    function(
        $,
        _,
        Backgrid,
        BaseRow,
        template
    ) {
        'use strict';

        /**
     * Grid row.
     *
     * Triggers events:
     *  - "clicked" when row is clicked
     *
     * @export  oro/datagrid/row
     * @class   oro.datagrid.Row
     * @extends Backgrid.Row
     */
        return BaseRow.extend({
            tagName: 'div',
            template: _.template(template),

            render() {
                this.$el.empty();
                const row = $(this.template({}));

                this.$el.append(row);

                for (let i = 0; i < this.cells.length; i++) {
                    const cell = this.cells[i];
                    this.$('.AknGrid-bodyRow').append(cell.render().el);
                }

                this.delegateEvents();

                return this;
            }
        });
    });
