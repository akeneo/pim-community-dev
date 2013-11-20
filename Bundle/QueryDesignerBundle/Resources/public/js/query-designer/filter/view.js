/* global define */
define(['oro/query-designer/abstract-view', 'oro/query-designer/filter/collection', 'oro/query-designer/filter-builder'],
function(AbstractView, FilterCollection, filterBuilder) {
    'use strict';

    /**
     * @export  oro/query-designer/filter/view
     * @class   oro.queryDesigner.filter.View
     * @extends oro.queryDesigner.AbstractView
     */
    return AbstractView.extend({
        /** @property oro.queryDesigner.filter.Collection */
        collectionClass: FilterCollection,

        /** @property {jQuery} */
        criterionSelector: null,

        initForm: function() {
            AbstractView.prototype.initForm.apply(this, arguments);

            this.criterionSelector = this.form.find('[data-purpose="criterion-selector"]');

            // load filters
            this.criterionSelector.hide();
            filterBuilder.init(this.criterionSelector.parent());

            // set criterion selector when a column changed
            this.getColumnSelector().on('change', _.bind(function (e) {
                if (!_.isUndefined(e.added)) {
                }
            }, this));
        }
    });
});
