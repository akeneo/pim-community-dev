var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};
Oro.Datagrid.Cell.Formatter = Oro.Datagrid.Cell.Formatter || {};

/**
 * Cell formatter with fixed fromRaw method
 *
 * @class   Oro.Datagrid.Cell.Formatter.CellFormatter
 * @extends Backgrid.CellFormatter
 */
Oro.Datagrid.Cell.Formatter.CellFormatter = function () {};

Oro.Datagrid.Cell.Formatter.CellFormatter.prototype = new Backgrid.CellFormatter;
_.extend(Oro.Datagrid.Cell.Formatter.CellFormatter.prototype, {
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
