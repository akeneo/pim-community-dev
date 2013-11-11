/* global define */
define(['oro/grid/string-cell', 'oro/grid/datetime-formatter'],
function(StringCell, DatagridDateTimeFormatter) {
    'use strict';

    /**
     * Datetime column cell
     *
     * @export  oro/grid/datetime-cell
     * @class   oro.grid.DateTimeCell
     * @extends oro.grid.StringCell
     */
    return StringCell.extend({
        /**
         * @property {oro.grid.DateTimeFormatter}
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
         * @return {oro.grid.DateTimeFormatter}
         */
        createFormatter: function() {
            return new this.formatterPrototype({type: this.type});
        }
    });
});
