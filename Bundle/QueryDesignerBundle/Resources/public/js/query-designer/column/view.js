/* global define */
define(['oro/query-designer/abstract-view', 'oro/query-designer/column/collection'],
function(AbstractView, ColumnCollection) {
    'use strict';

    /**
     * @export  oro/query-designer/column/view
     * @class   oro.queryDesigner.column.View
     * @extends oro.queryDesigner.AbstractView
     */
    return AbstractView.extend({
        /** @property oro.queryDesigner.column.Collection */
        collectionClass: ColumnCollection
    });
});
