/* global define */
define(['oro/translator', 'backgrid', 'pim/template/datagrid/cell/history-diff-cell'],
function(__, Backgrid, template) {
    'use strict';

    /**
     * History diff column cell.
     *
     * @export  oro/datagrid/history-diff-cell
     * @class   oro.datagrid.HistoryDiffCell
     * @extends Backgrid.StringCell
     */
    return Backgrid.StringCell.extend({
        template: _.template(template),

        /**
         * {@inheritdoc}
         */
        render: function () {
            this.el.setAttribute('colspan', 4);
            this.$el.empty();
            this.$el.html(this.template({
                changes: this.model.get(this.column.get('name')),
                __
            }));
            this.delegateEvents();

            return this;
        },
    });
});
