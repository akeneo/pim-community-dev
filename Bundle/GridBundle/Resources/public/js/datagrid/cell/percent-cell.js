/* global define */
define(['oro/grid/number-cell', 'oro/grid/percent-formatter'],
function(NumberCell, PercentFormatter) {
    'use strict';

    /**
     * Percent column cell. Renders numeric value with symbol %
     *
     * @export  oro/grid/percent-cell
     * @class   oro.grid.PercentCell
     * @extends oro.grid.NumberCell
     */
    return NumberCell.extend({
        formatter: PercentFormatter
    });
});
