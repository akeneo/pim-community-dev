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
                    this.listenTo(filter, "update", _.partial(this._onFilterUpdated, filter));
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
             * @protected
             */
            _onFilterUpdated: function(filter) {
                if (filter === this.activeFilter) {
                    this.trigger('update_value');
                }
            },

            /**
             * Sets a filter conforms the given criteria as active
             *
             * @param {Object} criteria
             */
            setActiveFilter: function (criteria) {
                var foundFilter = null;
                var foundFilterMatchedBy = null;
                _.each(this.options.filters, function(filter) {
                    var isApplicable = false;
                    if (!_.isEmpty(filter.applicable)) {
                        // if filter.applicable an array check if all items conforms the criteria
                        var matched = _.find(filter.applicable, function(applicable) {
                            var res = true;
                            _.each(applicable, function (val, key) {
                                if (!_.has(criteria, key) || !app.isEqualsLoosely(val, criteria[key])) {
                                    res = false;
                                }
                            });
                            return res;
                        });
                        if (!_.isUndefined(matched)) {
                            if (_.isNull(foundFilterMatchedBy)
                                // new rule is more exact
                                || _.size(foundFilterMatchedBy) < _.size(matched)
                                // 'type' rule is most low level one, so any other rule can override it
                                || (_.size(foundFilterMatchedBy) == 1 && _.has(foundFilterMatchedBy, 'type'))) {
                                foundFilterMatchedBy = matched;
                                isApplicable = true;
                            }
                        }
                    } else if (_.isNull(foundFilter)) {
                        // if a filter was nor found so far, use a default filter
                        isApplicable = true;
                    }
                    if (isApplicable) {
                        foundFilter = filter;
                    }
                });
                if (foundFilter !== this.activeFilter) {
                    if (!_.isNull(this.activeFilter)) {
                        this.activeFilter.hide();
                    }
                    this.activeFilter = foundFilter;
                    this.activeFilter.show();
                }
                this.activeFilter.reset();
            },

            /**
             * Returns a string representation of the given value
             *
             * @param {Object} value
             * @return {String}
             */
            getCriteriaHint: function(value) {
                return this.activeFilter._getCriteriaHint(value);
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
             * Determines whether a filter value is empty or not
             *
             * @return {Boolean}
             */
            isEmptyValue: function() {
                return this.activeFilter.isEmptyValue();
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
