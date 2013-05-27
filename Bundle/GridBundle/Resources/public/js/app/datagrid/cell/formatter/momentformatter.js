var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};
Oro.Datagrid.Cell.Formatter = Oro.Datagrid.Cell.Formatter || {};

/**
 * Formatter for date and time. Fixed formatting method.
 *
 * @class   Oro.Datagrid.Cell.Formatter.MomentFormatter
 * @extends Backgrid.Extension.MomentFormatter
 */
Oro.Datagrid.Cell.Formatter.MomentFormatter = function (options) {
    _.extend(this, this.defaults, options);
};

Oro.Datagrid.Cell.Formatter.MomentFormatter.prototype = new Backgrid.Extension.MomentFormatter;
_.extend(Oro.Datagrid.Cell.Formatter.MomentFormatter.prototype, {
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
