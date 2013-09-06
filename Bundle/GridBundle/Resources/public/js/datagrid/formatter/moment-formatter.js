/* global define */
define(['underscore', 'backgrid', 'backgrid/moment'],
function(_, Backgrid) {
    'use strict';

    /**
     * Formatter for date and time. Fixed formatting method.
     *
     * @export  oro/datagrid/moment-formatter
     * @class   oro.datagrid.MomentFormatter
     * @extends Backgrid.Extension.MomentFormatter
     */
    var MomentFormatter = function (options) {
        _.extend(this, this.defaults, options);
    };

    MomentFormatter.prototype = new Backgrid.Extension.MomentFormatter();

    _.extend(MomentFormatter.prototype, {
        /**
         * @inheritDoc
         */
        fromRaw: function (rawData) {
            if (!rawData) {
                return '';
            }
            return Backgrid.Extension.MomentFormatter.prototype.fromRaw.apply(this, arguments);
        }
    });

    return MomentFormatter;
});
