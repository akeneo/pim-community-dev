/* global define */
define(['oro/datagrid/string-cell'],
function(StringCell) {
    'use strict';

    /**
     * Html column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/html-cell
     * @class   oro.datagrid.HtmlCell
     * @extends oro.datagrid.StringCell
     */
    return StringCell.extend({
        /**
         * Render a text string in a table cell. The text is converted from the
         * model's raw value for this cell's column.
         */
        render: function () {
            this.$el.empty().html(this.formatter.fromRaw(this.model.get(this.column.get("name"))));
            return this;
        }
    });
});
