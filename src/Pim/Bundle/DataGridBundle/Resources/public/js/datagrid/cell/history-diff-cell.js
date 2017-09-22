/* global define */
define(['oro/translator', 'backgrid', 'pim/template/datagrid/cell/history-diff-cell'],
function(__, Backgrid, template) {
    'use strict';

    /**
     * String column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/string-cell
     * @class   oro.datagrid.StringCell
     * @extends Backgrid.StringCell
     */
    return Backgrid.StringCell.extend({
        template: _.template(template),
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
