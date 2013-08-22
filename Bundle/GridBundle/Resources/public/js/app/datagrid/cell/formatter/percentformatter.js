var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};
Oro.Datagrid.Cell.Formatter = Oro.Datagrid.Cell.Formatter || {};

/**
 * Percent formatter with extended fromRaw and toRaw methods
 *
 * @class   Oro.Datagrid.Cell.Formatter.PercentFormatter
 * @extends Backgrid.NumberFormatter
 */
Oro.Datagrid.Cell.Formatter.PercentFormatter = function () {};

Oro.Datagrid.Cell.Formatter.PercentFormatter.prototype = new Backgrid.NumberFormatter;
_.extend(Oro.Datagrid.Cell.Formatter.PercentFormatter.prototype, {
    /**
     * @inheritDoc
     */
    defaults: {
        decimals: 4,
        decimalSeparator: '.',
        orderSeparator: ','
    },

    /**
     * @inheritDoc
     */
    fromRaw: function (rawData) {

        if (rawData == null || rawData == '') {
            return '';
        }

        var data = rawData * 100;
        var value = Backgrid.NumberFormatter.prototype.fromRaw.call(this, data);

        return value + '%';
    },

    /**
     * @inheritDoc
     */
    toRaw: function (formattedData) {
        var value = formattedData.replace(/%$/, '');
        value = Backgrid.NumberFormatter.prototype.toRaw.call(this, value);

        return value / 100;
    }
});
