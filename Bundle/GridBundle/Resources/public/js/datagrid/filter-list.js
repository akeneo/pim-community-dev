/* global define */
define(['underscore', 'oro/datafilter/filters-manager'],
function(_, FilterList) {
    'use strict';

    /**
     * View that represents all grid filters
     *
     * @export  oro/grid/filter-list
     * @class   oro.grid.FilterList
     * @extends oro.FilterList
     */
    return FilterList.extend({
        /**
         * Initialize filter list options
         *
         * @param {Object} options
         * @param {oro.PageableCollection} [options.collection]
         * @param {Object} [options.filters]
         * @param {String} [options.addButtonHint]
         */
        initialize: function(options)
        {
            this.collection = options.collection;

            this.collection.on('beforeFetch', this._beforeCollectionFetch, this);
            this.collection.on('updateState', this._onUpdateCollectionState, this);
            this.collection.on('reset', this._onCollectionReset, this);

            FilterList.prototype.initialize.apply(this, arguments);
        },

        /**
         * Triggers when filter is updated
         *
         * @param {oro.filter.AbstractFilter} filter
         * @protected
         */
        _onFilterUpdated: function(filter) {
            if (this.ignoreFiltersUpdateEvents) {
                return;
            }
            this.collection.state.currentPage = 1;
            this.collection.fetch();

            FilterList.prototype._onFilterUpdated.apply(this, arguments);
        },

        /**
         * Triggers before collection fetch it's data
         *
         * @protected
         */
        _beforeCollectionFetch: function(collection) {
            collection.state.filters = this._createState();
        },

        /**
         * Triggers when collection state is updated
         *
         * @param {oro.PageableCollection} collection
         */
        _onUpdateCollectionState: function(collection) {
            this.ignoreFiltersUpdateEvents = true;
            this._applyState(collection.state.filters || {});
            this.ignoreFiltersUpdateEvents = false;
        },

        /**
         * Triggers after collection resets it's data
         *
         * @protected
         */
        _onCollectionReset: function(collection) {
            if (collection.state.totalRecords > 0 && this.$el.children().length > 0) {
                this.$el.show();
            }
        },

        /**
         * Create state according to filters parameters
         *
         * @return {Object}
         * @protected
         */
        _createState: function() {
            var state = {};
            _.each(this.filters, function(filter, name) {
                var shortName = '__' + name;
                if (filter.enabled) {
                    if (!filter.isEmpty()) {
                        state[name] = filter.getValue();
                    } else if (!filter.defaultEnabled) {
                        state[shortName] = 1;
                    }
                } else if (filter.defaultEnabled) {
                    state[shortName] = 0;
                }
            }, this);

            return state;
        },

        /**
         * Apply filter values from state
         *
         * @param {Object} state
         * @protected
         * @return {*}
         */
        _applyState: function(state) {
            var toEnable  = [],
                toDisable = [];

            _.each(this.filters, function(filter, name) {
                var shortName = '__' + name,
                    filterState;
                if (_.has(state, name)) {
                    filterState = state[name];
                    if (!_.isObject(filterState)) {
                        filterState = {
                            value: filterState
                        };
                    }
                    filter.setValue(filterState);
                    toEnable.push(filter);
                } else if (_.has(state, shortName)) {
                    filter.reset();
                    if (Number(state[shortName])) {
                        toEnable.push(filter);
                    } else {
                        toDisable.push(filter);
                    }
                } else {
                    filter.reset();
                    if (filter.defaultEnabled) {
                        toEnable.push(filter);
                    } else {
                        toDisable.push(filter);
                    }
                }
            }, this);

            this.enableFilters(toEnable);
            this.disableFilters(toDisable);

            return this;
        }
    });
});
