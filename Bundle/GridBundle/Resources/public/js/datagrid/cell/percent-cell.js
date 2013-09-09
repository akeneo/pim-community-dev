/* global define */
define(['oro/datagrid/number-cell', 'oro/datagrid/percent-formatter'],
function(NumberCell, PercentFormatter) {
    'use strict';

    /**
     * Percent column cell. Renders numeric value with symbol %
     *
     * @export  oro/datagrid/percent-cell
     * @class   oro.datagrid.PercentCell
     * @extends oro.datagrid.NumberCell
     */
    return NumberCell.extend({
        formatter: PercentFormatter
    });
});
