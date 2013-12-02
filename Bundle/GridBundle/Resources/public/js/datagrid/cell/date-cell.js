/* global define */
define(['oro/grid/datetime-cell'],
function(DatagridDateTimeCell) {
    'use strict';

    /**
     * Date column cell
     *
     * @export  oro/grid/date-cell
     * @class   oro.grid.DateCell
     * @extends oro.grid.DateTimeCell
     */
    return DatagridDateTimeCell.extend({type: 'date'});
});
