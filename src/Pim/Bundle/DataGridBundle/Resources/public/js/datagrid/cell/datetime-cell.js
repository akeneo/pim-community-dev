/* global define */
define(['oro/datagrid/string-cell', 'oro/datagrid/datetime-formatter'],
function(StringCell, DatagridDateTimeFormatter) {
    'use strict';

    /**
     * Datetime column cell
     *
     * @export  oro/datagrid/datetime-cell
     * @class   oro.datagrid.DateTimeCell
     * @extends oro.datagrid.StringCell
     */
    return StringCell.extend({
        /**
         * @property {oro.datagrid.DateTimeFormatter}
         */
        formatterPrototype: DatagridDateTimeFormatter,

        /**
         * @property {string}
         */
        type: 'dateTime',

        /**
         * @inheritDoc
         */
        initialize: function (options) {
            StringCell.prototype.initialize.apply(this, arguments);
            this.formatter = this.createFormatter();
        },

        /**
         * Creates number cell formatter
         *
         * @return {oro.datagrid.DateTimeFormatter}
         */
        createFormatter: function() {
            return new this.formatterPrototype({type: this.type});
        }
    });
});
