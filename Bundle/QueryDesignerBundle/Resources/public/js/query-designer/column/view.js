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
        collectionClass: ColumnCollection,

        initForm: function() {
            AbstractView.prototype.initForm.apply(this, arguments);

            // try to guess a label when a column changed
            this.getColumnSelector().on('change', _.bind(function (e) {
                if (!_.isUndefined(e.added)) {
                    var labelEl = this.findFormField('label');
                    if (labelEl.val() == ''
                        || (!_.isUndefined(e.removed) && !_.isUndefined(e.removed.text) && labelEl.val() == e.removed.text)) {
                        labelEl.val(e.added.text);
                    }
                }
            }, this));
        }
    });
});
