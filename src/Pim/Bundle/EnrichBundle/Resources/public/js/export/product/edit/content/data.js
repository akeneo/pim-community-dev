'use strict';
/**
 * This extension manages the data filter collection and its generation.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'text!pim/template/export/product/edit/content/data',
        'pim/form',
        'pim/fetcher-registry',
        'pim/form-builder'
    ],
    function (
        $,
        _,
        __,
        template,
        BaseForm,
        fetcherRegistry,
        formBuilder
    ) {
        return BaseForm.extend({
            filterViews: [],
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.onExtensions('add-attribute:add', function (event) {
                    this.addFilters(event.codes).then(function () {
                        this.updateModel();
                        this.render();
                    }.bind(this));
                }.bind(this));

                this.filterViews = [];

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments),
                    this.addConfigFilters()
                );
            },

            /**
             * Add a collection of filters
             *
             * @param {array} fieldCodes Can be attributes or product field codes
             */
            addFilters: function (fieldCodes) {
                // We pre-fetch the attributes to add to avoid multiple http requests
                return fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(fieldCodes)
                    .then(function () {
                        return $.when.apply($, _.map(fieldCodes, this.addFilter.bind(this)));
                    }.bind(this))
                    .then(function () {
                        this.updateFiltersData(_.extend({}, this.getFormData().data));
                    }.bind(this));
            },

            /**
             * Add a single filter
             *
             * @param {string} fieldCode
             */
            addFilter: function (fieldCode) {
                var deferred = $.Deferred();

                this.getFilterConfig(fieldCode)
                    .then(this.buildFilterView.bind(this))
                    .then(function (filterView) {
                        this.listenTo(filterView, 'pim_enrich:form:entity:post_update', this.updateModel.bind(this));
                        this.listenTo(filterView, 'filter:remove', this.removeFilter.bind(this));
                        this.listenTo(this.getRoot(), 'channel:update:after', function (scope) {
                            filterView.trigger('channel:update:after', scope);
                        }.bind(this));

                        this.filterViews.push(filterView);
                    }.bind(this))
                    .always(function () {
                        deferred.resolve();
                    });

                return deferred.promise();
            },

            /**
             * Build a filter view
             *
             * @param {Object} filterConfig
             *
             * @return {Promise}
             */
            buildFilterView: function (filterConfig) {
                return formBuilder.buildForm(filterConfig.view).then(function (filterView) {
                    filterView.setRemovable(filterConfig.isRemovable);
                    filterView.setType(filterConfig.view);
                    filterView.setParentForm(this);
                    filterView.setCode(filterConfig.field);

                    return filterView.configure().then(function () {
                        var data = {};
                        filterView.trigger('pim_enrich:form:entity:pre_update', data);
                        filterView.setData(data, {silent: true});

                        return filterView;
                    });
                }.bind(this));
            },

            /**
             * Get filter configuration for the giver field
             *
             * @param {string} fieldCode
             *
             * @return {Promise}
             */
            getFilterConfig: function (fieldCode) {
                var filterConfig = _.findWhere(this.config.filters, {field: fieldCode});

                if (undefined !== filterConfig) {
                    filterConfig.isRemovable = false;

                    return $.Deferred().resolve(filterConfig).promise();
                }

                return fetcherRegistry.getFetcher('attribute').fetch(fieldCode)
                    .then(function (attribute) {
                        return {
                            field: attribute.code,
                            /* jshint sub:true */
                            /* jscs:disable requireDotNotation */
                            view: attribute['filter_types']['product-export-builder'],
                            /* jscs:enable requireDotNotation */
                            /* jshint sub:false */
                            isRemovable: true
                        };
                    });
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template({__: __}));

                $.when(
                    fetcherRegistry.getFetcher('attribute').getIdentifierAttribute(),
                    this.addExistingFilters()
                ).then(function (identifier) {
                    var filtersContainer = this.$('.filters').empty();

                    var configuredFieldCodes = _.pluck(this.config.filters, 'field');
                    var savedFieldCodes = _.pluck(this.filterViews, 'filterCode').sort();
                    var fieldCodes = _.union(
                        configuredFieldCodes,
                        _.without(savedFieldCodes, identifier.code),
                        [identifier.code]
                    );

                    var filterViews = _.map(fieldCodes, function (code) {
                        var view = _.findWhere(this.filterViews, {filterCode: code});

                        if (undefined === view) {
                            return;
                        }

                        return view.render().$el;
                    }.bind(this));

                    filtersContainer.append(filterViews);

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Returns the current filters as an array of fields.
             *
             * @return {array}
             */
            getCurrentFilters: function () {
                return _.map(this.filterViews, function (filterView) {
                    return filterView.getCode();
                });
            },

            /**
             * Add filters from the configuration (the field filters and identifier)
             */
            addConfigFilters: function () {
                var promises = [];
                this.getRoot().trigger('pim_enrich:form:filter:set-default', promises);

                return $.when.apply($, promises).then(function () {
                    var defaultFieldCodes = 0 !== arguments.length ?
                        _.union(_.flatten(_.toArray(arguments))) :
                        [];
                    var configFilterCodes = _.pluck(this.config.filters, 'field');

                    return _.union(configFilterCodes, defaultFieldCodes);
                }.bind(this))
                .then(function (defaultFilterCodes) {
                    return this.addFilters(defaultFilterCodes);
                }.bind(this));
            },

            /**
             * Add filter stored in the backend (filters added by the user and saved)
             */
            addExistingFilters: function () {
                var filterCodes = _.map(_.pluck(this.getFormData().data, 'field'), function (field) {
                    return field.replace(/\.code$/, '');
                });

                var existingFilterCodes = _.map(this.filterViews, function (filterView) {
                    return filterView.getCode();
                });

                return this.addFilters(_.difference(filterCodes, existingFilterCodes));
            },

            /**
             * Returns default filter fields. They can be set by config or other extensions.
             *
             * @returns {Promise}
             */
            getDefaultFilterFields: function () {
                var promises = [];
                this.getRoot().trigger('pim_enrich:form:filter:set-default', promises);

                return $.when.apply($, promises).then(function () {
                    var defaultFields = 0 !== arguments.length ?
                        _.union(_.flatten(_.toArray(arguments))) :
                        [];
                    var configFields = _.pluck(this.config.filters, 'field');

                    return _.union(configFields, defaultFields);
                }.bind(this));
            },

            /**
             * Update the model of each filter views
             *
             * @param {Object} data
             */
            updateFiltersData: function (data) {
                _.each(this.filterViews, function (filterView) {
                    var filterData = _.findWhere(data, {field: filterView.getField()});
                    filterData = filterData || {};
                    filterView.trigger('pim_enrich:form:entity:pre_update', filterData);
                    filterView.setData(filterData, {silent: true});
                }.bind(this));

                this.updateModel();
            },

            /**
             * Updates the form model by iterating over filter views
             */
            updateModel: function () {
                var dataFilterCollection = [];

                _.each(this.filterViews, function (filterView) {
                    if (!filterView.isEmpty()) {
                        dataFilterCollection.push(filterView.getFormData());
                    }
                });

                this.setData({data: dataFilterCollection});
            },

            /**
             * Removes the filter for the given field then renders the whole view.
             *
             * @param {string} fieldCode
             */
            removeFilter: function (fieldCode) {
                this.filterViews = _.filter(this.filterViews, function (filterView) {
                    return filterView.getCode() !== fieldCode;
                });

                this.updateModel();
                this.render();
            }
        });
    }
);
