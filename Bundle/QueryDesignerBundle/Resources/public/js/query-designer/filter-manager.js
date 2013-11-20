/* global define */
define(['jquery', 'underscore', 'backbone', 'oro/app', 'oro/mediator'],
    function($, _, Backbone, app, mediator) {
        'use strict';

        /**
         * View that represents all query designer filter
         *
         * @export  oro/query-designer/filter-manager
         * @class   oro.queryDesigner.FilterManager
         * @extends Backbone.View
         */
        return Backbone.View.extend({
            /**
             * @property
             */
            tagName: 'div',

            /**
             * @property
             */
            className: 'filter-box oro-clearfix-width',

            /**
             * @property
             */
            activeFilter: null,

            /**
             * Initialize filter list options
             *
             * @param {Object} options
             * @param {Object} [options.filters] List of filter objects
             */
            initialize: function()
            {
                this.options.filters = this.options.filters || [];
                _.each(this.options.filters, function(filter) {
                    this.listenTo(filter, "update", this._onFilterUpdated);
                }, this);

                Backbone.View.prototype.initialize.apply(this, arguments);

                // destroy events bindings
                mediator.once('hash_navigation_request:start', function() {
                    _.each(this.filters, function(filter) {
                        this.stopListening(filter, "update", this._onFilterUpdated);
                    }, this);
                }, this);
            },

            /**
             * Triggers when filter is updated
             *
             * @param {oro.datafilter.AbstractFilter} filter
             * @protected
             */
            _onFilterUpdated: function(filter) {
                this.trigger('updateFilter', filter);
            },

            /**
             * Sets a filter conforms the given criteria as active
             *
             * @param {Object} criteria
             */
            setActiveFilter: function (criteria) {
                var newFilter = _.find(this.options.filters, function(filter) {
                    var isApplicable = false;
                    if (_.isFunction(filter.applicable)) {
                        // if filter.applicable is a function use it
                        isApplicable = filter.applicable(criteria);
                    } else if (_.isArray(filter.applicable) && !_.isEmpty(filter.applicable)) {
                        // if filter.applicable an array check if any item conforms the criteria
                        _.find(filter.applicable, function(applicable) {
                            return app.isEqualsLoosely(criteria, _.extend(applicable, criteria));
                        });
                    } else {
                        // otherwise; return default filter
                        isApplicable = true;
                    }
                    return isApplicable;
                });
                if (newFilter !== this.activeFilter) {
                    if (!_.isNull(this.activeFilter)) {
                        this.activeFilter.hide();
                    }
                    this.activeFilter = newFilter;
                    this.activeFilter.show();
                }
                this.activeFilter.reset();
            },

            /**
             * Returns a raw value of an active filter
             *
             * @returns {Object}
             */
            getValue: function() {
                return this.activeFilter.getValue();
            },

            /**
             * Sets a raw value for an active filter
             *
             * @param {Object} value
             */
            setValue: function(value) {
                this.activeFilter.setValue(value);
            },

            /**
             * Clears up a raw value for an active filter
             */
            resetValue: function() {
                this.activeFilter.reset();
            },

            /**
             * Render filter list
             *
             * @return {oro.queryDesigner.FilterHolder}
             */
            render: function () {
                this.$el.empty();
                var fragment = document.createDocumentFragment();

                _.each(this.options.filters, function(filter) {
                    filter.render();
                    filter.hide();
                    fragment.appendChild(filter.$el.get(0));
                }, this);
                this.$el.append(fragment);

                // activate default filter
                this.setActiveFilter({});

                this.trigger("rendered");

                return this;
            }
        });
    });
