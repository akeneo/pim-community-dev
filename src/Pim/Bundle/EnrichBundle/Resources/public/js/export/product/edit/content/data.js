'use strict';
/**
 * Data section
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'text!pim/template/export/product/edit/content/data',
        'pim/form',
        'pim/fetcher-registry',
        'pim/form-config-provider',
        'pim/form-builder'
    ],
    function (
        _,
        template,
        BaseForm,
        fetcherRegistry,
        configProvider,
        formBuilder
    ) {
        return BaseForm.extend({
            filterDataCollection: [],
            filterViews: {},
            template: _.template(template),
            initialize: function (config) {
                //change that after variant group merge
                this.config = config || {
                    filters: [
                        {field: 'enabled', view: 'pim-filter-text'},
                        {field: 'completeness', view: 'pim-filter-text'},
                        {field: 'family.code', view: 'pim-filter-product-family'}
                    ]
                };


                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.onExtensions('add-attribute:add', function (event) {
                    this.addFilters(event.codes);
                }.bind(this));

                $.when.apply($, _.map(this.config.filters, function (filter) {
                    return this.addFilterView(filter.view, filter.field, false);
                }.bind(this))).then(function () {
                    this.render();
                }.bind(this));

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.updateFiltersData.bind(this));

                BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({})
                );

                _.each(this.filterViews, function (filterView) {
                    this.$('.filters').append(filterView.render().$el);
                }.bind(this));

                this.renderExtensions();
            },
            addFilters: function (fields) {
                var fields = _.without(fields, _.keys(this.filterViews));

                return $.when(
                    fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(fields),
                    configProvider.getFilters(this.getRoot().code)
                ).then(function (attributes, config) {
                    return $.when.apply($, _.map(fields, function (field) {
                        var attribute = _.findWhere(attributes, {code: field})
                        var filterConfig = {};

                        if (undefined === attribute) {
                            filterConfig = _.findWhere(this.config.filters, {field: field});
                            filterConfig.removable = false;
                        } else {
                            filterConfig = {
                                field: attribute.code,
                                view: config[attribute.type].view,
                                removable: true
                            };
                        }

                        return this.addFilterView(filterConfig.view, filterConfig.field, filterConfig.removable);
                    }.bind(this)));
                }.bind(this)).then(function () {
                    this.render();
                }.bind(this));
            },
            addFilterView: function (viewCode, fieldCode, removable) {
                return formBuilder.build(viewCode).then(function(view) {
                    view.setField(fieldCode);
                    view.setRemovable(removable);

                    return view;
                }).then(function (filterView) {
                    var filterData = _.findWhere(this.filterDataCollection, {field: filterView.getField()});
                    if (null !== filterData) {
                        filterView.setData(filterData);
                    }

                    this.listenTo(filterView, 'pim_enrich:form:entity:post_update', this.updateModel.bind(this));
                    this.listenTo(filterView, 'filter:remove', this.removeFilter.bind(this));

                    this.filterViews[filterView.getField()] = filterView;

                    return filterView;
                }.bind(this));
            },
            updateModel: function () {
                this.filterDataCollection = [];

                _.each(this.filterViews, function (filterView, code) {
                    if (!_.isEmpty(filterView.getFormData())) {
                        this.filterDataCollection.push(filterView.getFormData());
                    }
                }.bind(this));

                var formData = this.getFormData();
                formData.data = this.filterDataCollection;
                this.setData(formData);
            },
            updateFiltersData: function () {
                var fields = _.map(this.getFormData()['data'], function (filter) {
                    return filter.field;
                });

                this.addFilters(fields).then(function () {
                    _.each(this.getFormData()['data'], function (filterData) {
                        var filterView = this.filterViews[filterData.field];

                        filterView.setData(filterData, {silent: true}).render();
                    }.bind(this));
                }.bind(this));
            },
            removeFilter: function (field) {
                delete this.filterViews[field];
                this.updateModel();

                this.render();
            }
        });
    }
);
