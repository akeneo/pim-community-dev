/* global define */
define(['oro/datagrid/string-cell'],
function(StringCell) {
    'use strict';

    /**
     * Datetime column cell
     *
     * @export  oro/datagrid/datetime-cell
     * @class   oro.datagrid.DateTimeCell
     * @extends oro.datagrid.StringCell
     */
    return StringCell.extend({type: 'dateTime'});
});
