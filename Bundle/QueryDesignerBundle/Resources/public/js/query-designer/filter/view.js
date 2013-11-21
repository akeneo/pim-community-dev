/* global define */
define(['oro/query-designer/abstract-view', 'oro/query-designer/filter/collection', 'oro/query-designer/filter-builder'],
function(AbstractView, FilterCollection, filterBuilder) {
    'use strict';

    var $ = Backbone.$;

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

        /** @property {oro.queryDesigner.FilterManager} */
        filterManager: null,

        initialize: function() {
            AbstractView.prototype.initialize.apply(this, arguments);
            this.addFieldLabelGetter(this.getCriterionFieldLabel);
        },

        initForm: function() {
            AbstractView.prototype.initForm.apply(this, arguments);

            this.criterionSelector = this.form.find('[data-purpose="criterion-selector"]');

            // load filters
            this.criterionSelector.hide();
            filterBuilder.init(this.criterionSelector.parent(), _.bind(function (filterManager) {
                this.filterManager = filterManager;
                this.listenTo(this.filterManager, "update_value", this.onCriterionValueUpdated);
            }, this));

            // set criterion selector when a column changed
            this.getColumnSelector().on('change', _.bind(function (e) {
                if (!_.isUndefined(e.added)) {
                    if (_.isNull(this.filterManager) && !_.isUndefined(console)) {
                        console.error('Cannot choose a filer because the filter manager was not initialized yet.');
                    } else {
                        var criteria = _.extend(
                            {field: e.added.id, entity: this.options.entityName},
                            $(e.added.element).data()
                        );
                        this.filterManager.setActiveFilter(criteria);
                    }
                }
            }, this));
        },

        onCriterionValueUpdated: function () {
            this.criterionSelector.val(
                this.filterManager.isEmptyValue()
                    ? ''
                    : JSON.stringify(this.filterManager.getValue())
            );
        },

        prepareItemTemplateData: function (model) {
            var data = AbstractView.prototype.prepareItemTemplateData.apply(this, arguments);
            data['filter'] = data['columnName'] + ' ' + data['criterion'];
            return data;
        },

        getCriterionFieldLabel: function (field, name, value) {
            if (field.attr('name') == this.criterionSelector.attr('name')) {
                return (value != '')
                    ? this.filterManager.getCriteriaHint(JSON.parse(value))
                    : '';
            }
            return null;
        }
    });
});
