/* global define */
define(['oro/datagrid/datetime-cell'],
function(DatagridDateTimeCell) {
    'use strict';

    /**
     * Date column cell
     *
     * @export  oro/datagrid/date-cell
     * @class   oro.datagrid.DateCell
     * @extends oro.datagrid.DateTimeCell
     */
    return DatagridDateTimeCell.extend({type: 'date'});
});
