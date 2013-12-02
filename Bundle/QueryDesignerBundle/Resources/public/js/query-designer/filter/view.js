/* global define */
define(['underscore', 'oro/translator', 'oro/query-designer/abstract-view', 'oro/query-designer/filter/collection', 'oro/query-designer/filter-builder'],
function(_, __, AbstractView, FilterCollection, filterBuilder) {
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

        /** @property {jQuery} */
        filtersLogicEl: null,

        initialize: function() {
            AbstractView.prototype.initialize.apply(this, arguments);
            this.addFieldLabelGetter(this.getCriterionFieldLabel);
        },

        initForm: function() {
            AbstractView.prototype.initForm.apply(this, arguments);

            this.criterionSelector = this.form.find('[data-purpose="criterion-selector"]');
            this.filtersLogicEl = this.form.parent().find('[data-purpose="filter-logic"]');

            // load filters
            this.criterionSelector.hide();
            filterBuilder.init(this.criterionSelector.parent(), _.bind(function (filterManager) {
                this.filterManager = filterManager;
                this.listenTo(this.filterManager, "update_value", this.onCriterionValueUpdated);
                this.trigger('filter_manager_initialized');
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

            // set criterion selector when underlined input control changed
            this.criterionSelector.on('change', _.bind(function (e) {
                if (_.isNull(this.filterManager) && !_.isUndefined(console)) {
                    console.error('Cannot set a filter because the filter manager was not initialized yet.');
                } else {
                    if (e.currentTarget.value == '') {
                        this.filterManager.reset();
                    } else {
                        this.filterManager.setActiveFilter(JSON.parse(e.currentTarget.value));
                    }
                }
            }, this));
        },

        beforeFormSubmit: function () {
            if (!_.isNull(this.filterManager)) {
                this.filterManager.ensurePopupCriteriaClosed();
            }
            AbstractView.prototype.beforeFormSubmit.apply(this, arguments);
        },

        getFiltersLogic: function () {
            return this.filtersLogicEl.val();
        },

        setFiltersLogic: function (str) {
            this.filtersLogicEl.val(str);
        },

        initModel: function (model, index) {
            AbstractView.prototype.initModel.apply(this, arguments);
            model.set('index', index + 1);
        },

        addModel: function(model) {
            AbstractView.prototype.addModel.apply(this, arguments);
            if (this.filtersLogicEl.val() == '') {
                this.filtersLogicEl.val(this.filtersLogicEl.val() + model.get('index'));
            } else {
                this.filtersLogicEl.val(this.filtersLogicEl.val() + ' AND ' + model.get('index'));
            }
        },

        deleteModel: function(model) {
            AbstractView.prototype.deleteModel.apply(this, arguments);
            this.getCollection().each(function (m) {
                if (m.get('index') > model.get('index')) {
                    m.set('index', m.get('index') - 1);
                }
            });

            // try to remove the deleted filter from filters logic
            var filtersLogic = this.getFiltersLogic();
            var newFiltersLogic = filtersLogic;
            var index = '' + model.get('index');
            if (filtersLogic == index) {
                newFiltersLogic = '';
            } else {
                var replacers = [
                    {pattern: '^\\(' + index + '\\)$',          replaceValue: ''},
                    {pattern: ' (AND|OR) \\(' + index + '\\) ', replaceValue: ' '},
                    {pattern: ' (AND|OR) \\(' + index + '\\)$', replaceValue: ''},
                    {pattern: '^\\(' + index + '\\) (AND|OR) ', replaceValue: ''},
                    {pattern: ' (AND|OR) ' + index + ' ',       replaceValue: ' '},
                    {pattern: ' (AND|OR) ' + index + '\\)',     replaceValue: ')'},
                    {pattern: ' (AND|OR) ' + index + '$',       replaceValue: ''},
                    {pattern: ' ' + index + ' (AND|OR) ',       replaceValue: ' '},
                    {pattern: '\\(' + index + ' (AND|OR) ',     replaceValue: '('},
                    {pattern: '^' + index + ' (AND|OR) ',       replaceValue: ''}
                ];
                _.each(replacers, function (replacer) {
                    newFiltersLogic = newFiltersLogic.replace(new RegExp(replacer.pattern, 'i') , replacer.replaceValue);
                });
            }
            if (newFiltersLogic != filtersLogic) {
                index = Number(index);
                newFiltersLogic = newFiltersLogic.replace(/(\d+)/g, function (match) {
                    if (Number(match) > index) {
                        return '' + (Number(match) - 1);
                    }
                    return match;
                });
                this.setFiltersLogic(newFiltersLogic);
            }
        },

        onResetCollection: function () {
            if (!_.isNull(this.filterManager)) {
                AbstractView.prototype.onResetCollection.apply(this, arguments);
                if (this.getCollection().isEmpty()) {
                    this.setFiltersLogic('');
                }
            } else {
                this.once('filter_manager_initialized', function() {
                    AbstractView.prototype.onResetCollection.apply(this, arguments);
                    if (this.getCollection().isEmpty()) {
                        this.setFiltersLogic('');
                    }
                }, this);
            }
        },

        onCriterionValueUpdated: function () {
            this.criterionSelector.val(
                this.filterManager.isEmptyValue()
                    ? ''
                    : JSON.stringify({
                        filter: this.filterManager.getName(),
                        data: this.filterManager.getValue()
                      })
            );
        },

        prepareItemTemplateData: function (model) {
            var data = AbstractView.prototype.prepareItemTemplateData.apply(this, arguments);
            data['filter'] = data['columnName'] + ' ' + data['criterion'];
            return data;
        },

        getCriterionFieldLabel: function (field, name, value) {
            if (field.attr('name') == this.criterionSelector.attr('name')) {
                if (_.isNull(value) || value == '') {
                    return '';
                } else if (_.isString(value)) {
                    value = JSON.parse(value)
                }
                return this.filterManager.getCriteriaHint(value);
            }
            return null;
        },

        getFormFieldValue: function (name, field) {
            if (field.attr('name') == this.criterionSelector.attr('name')) {
                var value = field.val();
                return (value != '') ? JSON.parse(value) : null;
            }
            return AbstractView.prototype.getFormFieldValue.apply(this, arguments);
        },

        setFormFieldValue: function (name, field, value) {
            if (field.attr('name') == this.criterionSelector.attr('name')) {
                if (_.isNull(value) || value == '') {
                    field.val('');
                } else {
                    field.val(JSON.stringify(value));
                }
                return;
            }
            AbstractView.prototype.setFormFieldValue.apply(this, arguments);
        }
    });
});
