/* global define */
define(['underscore', 'backgrid'],
function(_, Backgrid) {
    'use strict';

    /**
     * Cell formatter with fixed fromRaw method
     *
     * @export  oro/datagrid/cell-formatter
     * @class   oro.datagrid.CellFormatter
     * @extends Backgrid.CellFormatter
     */
    var CellFormatter = function () {};

    CellFormatter.prototype = new Backgrid.CellFormatter();

    _.extend(CellFormatter.prototype, {
        /**
         * @inheritDoc
         */
        fromRaw: function (rawData) {
            if (rawData == null) {
                return '';
            }
            return Backgrid.CellFormatter.prototype.fromRaw.apply(this, arguments);
        }
    });

    return CellFormatter;
});
