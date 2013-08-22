var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};

/**
 * Percent column cell. Renders numeric value with symbol %
 *
 * @class   Oro.Datagrid.Cell.PercentCell
 * @extends Oro.Datagrid.Cell.NumberCell
 */
Oro.Datagrid.Cell.PercentCell = Oro.Datagrid.Cell.NumberCell.extend({
    /** @property {Oro.Datagrid.Cell.Formatter.PercentFormatter} */
    formatter: Oro.Datagrid.Cell.Formatter.PercentFormatter
});
