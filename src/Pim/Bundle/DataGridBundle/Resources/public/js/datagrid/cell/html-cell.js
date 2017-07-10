/* global define */
import StringCell from 'oro/datagrid/string-cell';
    

    /**
     * Html column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/html-cell
     * @class   oro.datagrid.HtmlCell
     * @extends oro.datagrid.StringCell
     */
    export default StringCell.extend({
        /**
         * Render a text string in a table cell. The text is converted from the
         * model's raw value for this cell's column.
         */
        render: function () {
            this.$el.empty().html(this.formatter.fromRaw(this.model.get(this.column.get("name"))));
            return this;
        }
    });

