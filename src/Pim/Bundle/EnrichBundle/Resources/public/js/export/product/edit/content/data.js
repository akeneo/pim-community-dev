'use strict';
/**
 *This extension manage the data filter collection and it's generation
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
            filterViews: {},
            template: _.template(template),

            /**
             * {@inherit}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            configure: function () {
                this.onExtensions('add-attribute:add', function (event) {
                    this.addFilters(event.codes);
                }.bind(this));

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.updateFiltersData.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template());

                _.each(this.filterViews, function (filterView) {
                    filterView.setParentForm(this);
                    this.$('.filters').append(filterView.render().$el);
                }.bind(this));

                this.renderExtensions();
            },

            /**
             * Adds filters to the form for the specified fields.
             *
             * @param {array} fields
             *
             * @return {Promise}
             */
            addFilters: function (fields) {
                var filterFields = _.difference(fields, _.keys(this.filterViews));
                if (_.isEmpty(filterFields)) {
                    return $.Deferred().resolve();
                }

                return this.getFiltersConfig(filterFields)
                    .then(function (filtersConfig) {
                        var promises = [];
                        _.each(filtersConfig, function (filterConfig) {
                            promises.push(
                                this.addFilterView(
                                    filterConfig.view,
                                    filterConfig.field,
                                    filterConfig.isRemovable
                                )
                            );
                        }.bind(this));

                        return $.when.apply($, promises);
                    }.bind(this));
            },

            /**
             * Returns the filters configuration corresponding to the specified fields.
             * The config can come from an attribute or from this extension's config (e.g. family, completeness, etc.).
             *
             * @param {Array} filterFields
             *
             * @return {Promise}
             */
            getFiltersConfig: function (filterFields) {
                return $.when(
                    fetcherRegistry.getFetcher('attribute').fetchByIdentifiers(filterFields),
                    configProvider.getFilters(this.getRoot().code)
                ).then(function (attributes, config) {
                    var filtersConfig = [];

                    _.each(filterFields, function (field) {
                        var attribute    = _.findWhere(attributes, {code: field});
                        var filterConfig = {};

                        if (undefined === attribute) {
                            filterConfig = _.findWhere(this.config.filters, {field: field});
                            if (undefined === filterConfig) {
                                return;
                            }

                            filterConfig.isRemovable = false;
                        } else {
                            filterConfig = {
                                field: attribute.code,
                                view: config[attribute.type].view,
                                isRemovable: true
                            };
                        }

                        filtersConfig.push(filterConfig);
                    }.bind(this));

                    return filtersConfig;
                }.bind(this));
            },

            /**
             * Creates and add the filter view to the form.
             *
             * @param {string}  viewCode
             * @param {string}  fieldCode
             * @param {boolean} isRemovable
             *
             * @return {Promise}
             */
            addFilterView: function (viewCode, fieldCode, isRemovable) {
                return formBuilder.build(viewCode).then(function(view) {
                    view.setField(fieldCode);
                    view.setRemovable(isRemovable);
                    view.setType(viewCode);

                    return view;
                }).then(function (filterView) {
                    var filterData = _.findWhere(this.getFormData().data, {field: filterView.getField()});
                    if (undefined !== filterData) {
                        filterView.setData(filterData);
                    }

                    this.listenTo(filterView, 'pim_enrich:form:entity:post_update', this.updateModel.bind(this));
                    this.listenTo(filterView, 'filter:remove', this.removeFilter.bind(this));
                    this.listenTo(this.getRoot(), 'channel:update:after', function (scope) {
                        filterView.trigger('channel:update:after', scope)
                    }.bind(this));

                    this.filterViews[filterView.getField()] = filterView;

                    return filterView;
                }.bind(this));
            },

            /**
             * Updates the form model.
             */
            updateModel: function () {
                var dataFilterCollection = [];

                _.each(this.filterViews, function (filterView) {
                    if (!filterView.isEmpty()) {
                        dataFilterCollection.push(filterView.getFormData());
                    }
                }.bind(this));

                this.setData({data: dataFilterCollection});
            },

            /**
             * Sets back the data to the filters view.
             */
            updateFiltersData: function () {
                var promises = [];
                this.getRoot().trigger('pim_enrich:form:filter:set-default', promises);

                $.when.apply($, promises).then(function () {
                    var defaultFields = 0 !== arguments.length ?
                        _.union(_.flatten(_.toArray(arguments))) :
                        [];
                    var configFields  = _.pluck(this.config.filters, 'field');

                    return _.union(configFields, defaultFields);
                }.bind(this)).then(function (defaultFields) {
                    var modelFields   = _.pluck(this.getFormData()['data'], 'field');

                    this.addFilters(_.union(defaultFields, modelFields)).then(function () {
                        _.each(this.getFormData()['data'], function (filterData) {
                            if (!_.has(this.filterViews, filterData.field)) {
                                return;
                            }
                            var filterView = this.filterViews[filterData.field];

                            filterView.setData(filterData, {silent: true});
                        }.bind(this));

                        this.render();
                    }.bind(this));
                }.bind(this))
            },

            /**
             * Removes the filter for the given field then renders the whole view.
             *
             * @param {string} field
             */
            removeFilter: function (field) {
                delete this.filterViews[field];
                this.updateModel();

                this.render();
            }
        });
    }
);
