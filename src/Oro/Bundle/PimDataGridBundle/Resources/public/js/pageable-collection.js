/* global define */
define(['underscore', 'backbone', 'backbone/pageable-collection', 'oro/app'],
function(_, Backbone, BackbonePageableCollection, app) {
    'use strict';

    /**
     * Pageable collection
     *
     * Additional events:
     * beforeReset: Fired when collection is about to reset data (e.g. after success response)
     * updateState: Fired when collection state is updated using updateState method
     * beforeFetch: Fired when collection starts to fetch, before request is formed
     *
     * @export  oro/pageable-collection
     * @class   oro.PageableCollection
     * @extends Backbone.PageableCollection
     */
    var PageableCollection = BackbonePageableCollection.extend({
        /**
         * Basic model to store row data
         *
         * @property {Function}
         */
        model: Backbone.Model,

        /**
         * Initial state of collection
         *
         * @property
         */
        initialState: {},

        /**
         * Declaration of URL parameters
         *
         * @property {Object}
         */
        queryParams: _.extend({}, BackbonePageableCollection.prototype.queryParams, {
            directions: {
                "-1": "ASC",
                "1": "DESC"
            },
            totalRecords: undefined,
            totalPages: undefined
        }),

        /**
         * Object declares state keys that will be involved in URL-state saving with their shorthands
         *
         * @property {Object}
         */
        stateShortKeys: {
            currentPage: 'i',
            pageSize: 'p',
            sorters: 's',
            filters: 'f',
            gridName: 't',
            gridView: 'v'
        },

        /**
         * @property {Object}
         */
        additionalParameters: {
            view: 'gridView'
        },

        /**
         * Whether multiple sorting is allowed
         *
         * @property {Boolean}
         */
        multipleSorting: true,

        /**
         * Initialize basic parameters from source options
         *
         * @param models
         * @param options
         */
        initialize: function(models, options) {
            this.state = {};

            options = options || {};
            if (options.state) {
                if (options.state.sorters) {
                    _.each(options.state.sorters, function(direction, field) {
                        options.state.sorters[field] = this.getSortDirectionKey(direction);
                    }, this);
                }
                _.extend(this.state, options.state);
            }

            this.initialState = app.deepClone(this.state);

            if (options.url) {
                this.url = options.url;
            }
            if (options.model) {
                this.model = options.model;
            }
            if (options.inputName) {
                this.inputName = options.inputName;
            }

            _.extend(this.queryParams, {
                currentPage: this.inputName + '[_pager][_page]',
                pageSize:    this.inputName + '[_pager][_per_page]',
                sortBy:      this.inputName + '[_sort_by][%field%]',
                parameters:  this.inputName + '[_parameters]'
            });

            this.on('remove', this.onRemove, this);

            BackbonePageableCollection.prototype.initialize.apply(this, arguments);
        },

        /**
         * Triggers when model is removed from collection.
         *
         * Ensure that state is changed after concrete model removed from collection.
         *
         * @protected
         */
        onRemove: function() {
            if (this.state.totalRecords > 0) {
                this.state.totalRecords--;
            }
        },

        /**
         * Encode state object to string
         *
         * @param {Object} stateObject
         * @return {String}
         */
        encodeStateData: function(stateObject) {
            var data = _.pick(stateObject, _.keys(this.stateShortKeys));
            data.gridName = this.inputName;
            data = app.invertKeys(data, this.stateShortKeys);
            return app.packToQueryString(data);
        },

        /**
         * Decode state object from string, operation is invert for encodeStateData.
         *
         * @param {String} stateString
         * @return {Object}
         */
        decodeStateData: function(stateString) {
            var data = app.unpackFromQueryString(stateString);
            data = app.invertKeys(data, _.invert(this.stateShortKeys));
            return data;
        },

        /**
         * Adds filter parameters to data
         *
         * @param {Object} data
         * @param {Object} state
         * @param {String} prefix
         * @return {Object}
         */
        processFiltersParams: function(data, state, prefix) {
            if (!state) {
                state = this.state;
            }

            if (!prefix) {
                prefix = this.inputName + '[_filter]'
            }

            if (this.inputName &&
                !_.isUndefined(data[this.inputName]) &&
                !_.isUndefined(data[this.inputName]._filter)
            ) {
                const scope = !_.isUndefined(data[this.inputName]._filter.scope) ?
                    data[this.inputName]._filter.scope :
                    null;

                data[this.inputName]._filter = {};

                if (null !== scope) {
                    data[this.inputName]._filter.scope = scope
                }
            }
            if (state.filters) {
                _.extend(
                    data,
                    this.generateParameterStrings(state.filters, prefix)
                );
            }
            return data;
        },

        /**
         * Adds additional parameters to state
         *
         * @param {Object} state
         * @return {Object}
         */
        processAdditionalParams: function (state) {
            var state = app.deepClone(state);
            state.parameters = state.parameters || {};

            _.each(this.additionalParameters, _.bind(function(value, key) {
                if (!_.isUndefined(state[value])) {
                    state.parameters[key] = state[value]
                }
            }, this));

            return state;
        },

        /**
         * Get list of request parameters
         *
         * @param {Object} parameters
         * @param {String} prefix
         * @return {Object}
         */
        generateParameterStrings: function(parameters, prefix) {
            var localStrings = {};
            var localPrefix = prefix;
            _.each(parameters, function(filterParameters, filterKey) {
                filterKey = filterKey.toString();
                if (filterKey.substr(0, 2) != '__') {
                    var filterKeyString = localPrefix + '[' + filterKey + ']';
                    if (_.isObject(filterParameters)) {
                        _.extend(
                            localStrings,
                            this.generateParameterStrings(filterParameters, filterKeyString)
                        );
                    } else {
                        localStrings[filterKeyString] = filterParameters;
                    }
                }
            }, this);

            return localStrings;
        },

        /**
         * Parse AJAX response
         *
         * @param resp
         * @param options
         * @return {Object}
         */
        parse: function(resp, options) {
            this.state.totalRecords = 'undefined' !== typeof(resp.totalRecords) ? resp.totalRecords : resp.options.totalRecords;
            this.state = this._checkState(this.state);
            return resp.data;
        },

        /**
         * Reset collection object
         *
         * @param models
         * @param options
         */
        reset: function(models, options) {
            this.trigger('beforeReset', this, options);
            BackbonePageableCollection.prototype.reset.apply(this, arguments);
        },

        /**
         * Updates and checks state
         *
         * @param {Object} state
         */
        updateState: function(state) {
            var newState = _.extend({}, this.state, state);
            this.state = this._checkState(newState);
            this.trigger('updateState', this, this.state);
        },

        /**
         * @inheritDoc
         */
        _checkState: function (state) {
            var mode = this.mode;
            var links = this.links;
            var totalRecords = state.totalRecords;
            var pageSize = state.pageSize;
            var currentPage = (state.currentPage < 0) ? 1 : state.currentPage;
            var firstPage = state.firstPage;
            var totalPages = state.totalPages;

            if (totalRecords != null && pageSize != null && currentPage != null &&
                firstPage != null && (mode == "infinite" ? links : true)) {

                state.totalRecords = totalRecords = this.finiteInt(totalRecords, "totalRecords");
                state.pageSize = pageSize = this.finiteInt(pageSize, "pageSize");
                state.currentPage = currentPage = this.finiteInt(currentPage, "currentPage");
                state.firstPage = firstPage = this.finiteInt(firstPage, "firstPage");

                if (pageSize < 0) {
                    throw new RangeError("`pageSize` must be >= 0");
                }

                state.totalPages = pageSize == 0 ? 1 : totalPages = state.totalPages = Math.ceil(totalRecords / pageSize);

                if (firstPage < 0 || firstPage > 1) {
                    throw new RangeError("`firstPage` must be 0 or 1");
                }

                state.lastPage = firstPage === 0 ? totalPages - 1 : totalPages;
                state.lastPage = state.lastPage < 0 ? 1 : state.lastPage;

                // page out of range
                if (currentPage > state.lastPage && state.pageSize > 0) {
                    state.currentPage = currentPage = state.lastPage;
                }

                if (state.pageSize == 0) {
                    state.currentPage = currentPage = 1;
                }

                // no results returned
                if (totalRecords == 0) {
                    state.currentPage = currentPage = firstPage;
                }

                if (mode == "infinite") {
                    if (!links[currentPage + '']) {
                        throw new RangeError("No link found for page " + currentPage);
                    }
                } else if (totalPages > 0) {
                    if (firstPage === 0 && (currentPage < firstPage || currentPage >= totalPages)) {
                        throw new RangeError("`currentPage` must be firstPage <= currentPage < totalPages if 0-based. Got " + currentPage + '.');
                    }
                    else if (firstPage === 1 && (currentPage < firstPage || currentPage > totalPages)) {
                        throw new RangeError("`currentPage` must be firstPage <= currentPage <= totalPages if 1-based. Got " + currentPage + '.');
                    }
                } else if (currentPage !== firstPage ) {
                    throw new RangeError("`currentPage` must be " + firstPage + ". Got " + currentPage + '.');
                }
            }

            return state;
        },

        /**
         * Asserts that val is finite integer.
         *
         * @param {*} val
         * @param {String} name
         * @return {Number}
         * @protected
         */
        finiteInt: function(val, name) {
            val *= 1;
            if (!_.isNumber(val) || _.isNaN(val) || !_.isFinite(val) || ~~val !== val) {
                throw new TypeError("`" + name + "` must be a finite integer");
            }
            return val;
        },

        /**
         * Fetch collection data
         */
        fetch: function (options) {
            this.trigger('beforeFetch', this, options);
            var BBColProto = Backbone.Collection.prototype;

            options = options || {};

            var state = this._checkState(this.state);

            var mode = this.mode;

            if (mode == "infinite" && !options.url) {
                options.url = this.links[state.currentPage];
            }

            var data = options.data || {};

            // set up query params
            var url = options.url || _.result(this, "url") || '';
            var qsi = url.indexOf('?');
            if (qsi != -1) {
                _.extend(data, app.unpackFromQueryString(url.slice(qsi + 1)));
                url = url.slice(0, qsi);
            }

            options.url = url;
            options.data = data;

            data = this.processQueryParams(data, state);
            data = this.processFiltersParams(data, state);

            var fullCollection = this.fullCollection, links = this.links;

            if (mode != "server") {

                var self = this;
                var success = options.success;
                options.success = function (col, resp, opts) {

                    // make sure the caller's intent is obeyed
                    opts = opts || {};
                    if (_.isUndefined(options.silent)) delete opts.silent;
                    else opts.silent = options.silent;

                    var models = col.models;
                    var currentPage = state.currentPage;

                if (mode == "client") resetQuickly(fullCollection, models, opts);
                    else if (links[currentPage]) { // refetching a page
                        var pageSize = state.pageSize;
                        var pageStart = (state.firstPage === 0 ?
                            currentPage :
                            currentPage - 1) * pageSize;
                        var fullModels = fullCollection.models;
                        var head = fullModels.slice(0, pageStart);
                        var tail = fullModels.slice(pageStart + pageSize);
                        fullModels = head.concat(models).concat(tail);
                        fullCollection.update(fullModels,
                            _.extend({silent: true, sort: false}, opts));
                        if (fullCollection.comparator) fullCollection.sort();
                        fullCollection.trigger("reset", fullCollection, opts);
                    }
                    else { // fetching new page
                        fullCollection.add(models, _.extend({at: fullCollection.length,
                            silent: true}, opts));
                        fullCollection.trigger("reset", fullCollection, opts);
                    }

                    if (success) success(col, resp, opts);
                };

                // silent the first reset from backbone
                return BBColProto.fetch.call(self, _.extend({}, options, {silent: true}));
            }

            return BBColProto.fetch.call(this, options);
        },

        /**
         * Process parameters which are sending to server
         *
         * @param {Object} data
         * @param {Object} state
         * @return {Object}
         */
        processQueryParams: function(data, state) {
            state = this.processAdditionalParams(state);
            var pageablePrototype = PageableCollection.prototype;

            // map params except directions
            var queryParams = this.mode == "client" ?
                _.pick(this.queryParams, "sorters") :
                _.omit(_.pick(this.queryParams, _.keys(pageablePrototype.queryParams)), "directions");

            var i, kvp, k, v, kvps = _.pairs(queryParams), thisCopy = _.clone(this);
            for (i = 0; i < kvps.length; i++) {
                kvp = kvps[i], k = kvp[0], v = kvp[1];
                v = _.isFunction(v) ? v.call(thisCopy) : v;
                if (state[k] != null && v != null) {
                    data[v] = state[k];
                }
            }

            // set sorting parameters
            if (state.sorters) {
                _.each(state.sorters, function(direction, field) {
                    var key = this.queryParams.sortBy.replace('%field%', field);
                    data[key] = this.queryParams.directions[direction];
                }, this);
            }

            // map extra query parameters
            var extraKvps = _.pairs(_.omit(this.queryParams,
                _.keys(pageablePrototype.queryParams)));
            for (i = 0; i < extraKvps.length; i++) {
                kvp = extraKvps[i];
                v = kvp[1];
                v = _.isFunction(v) ? v.call(thisCopy) : v;
                data[kvp[0]] = v;
            }

            // unused parameters
            delete data[queryParams.order];
            delete data[queryParams.sortKey];

            return data;
        },

        /**
         * Convert direction value to direction key
         *
         * @param {String} directionValue
         * @return {String}
         */
        getSortDirectionKey: function(directionValue) {
            var directionKey = null;
            _.each(this.queryParams.directions, function(value, key) {
                if (value == directionValue) {
                    directionKey = key;
                }
            });
            return directionKey;
        },

        /**
         * Set sorting order
         *
         * @param {String} sortKey
         * @param {String} order
         * @param {Object} options
         * @return {*}
         */
        setSorting: function (sortKey, order, options) {
            var state = this.state;

            state.sorters = state.sorters || {};

            if (this.multipleSorting) {
                // there is always must be at least one sorted column
                if (_.keys(state.sorters).length <= 1 && !order) {
                    order = this.getSortDirectionKey("ASC");  // default order
                }

                // last sorting has the lowest priority
                delete state.sorters[sortKey];
            } else {
                state.sorters = {};
            }

            if (order) {
                state.sorters[sortKey] = order;
            }

            var fullCollection = this.fullCollection;

            var delComp = false, delFullComp = false;

            if (!order) delComp = delFullComp = true;

            var mode = this.mode;
            options = _.extend({side: mode == "client" ? mode : "server", full: true},
                options
            );

            var comparator = this._makeComparator(sortKey, order);

            var full = options.full, side = options.side;

            if (side == "client") {
                if (full) {
                    if (fullCollection) fullCollection.comparator = comparator;
                    delComp = true;
                }
                else {
                    this.comparator = comparator;
                    delFullComp = true;
                }
            }
            else if (side == "server" && !full) {
                this.comparator = comparator;
            }

            if (delComp) delete this.comparator;
            if (delFullComp && fullCollection) delete fullCollection.comparator;

            return this;
        },
        /**
         * Clone collection
         *
         * @return {PageableCollection}
         */
        clone: function() {
            var collectionOptions = {};
            collectionOptions.url = this.url;
            collectionOptions.inputName = this.inputName;
            var newCollection = new PageableCollection(this.toJSON(), collectionOptions);
            newCollection.state = app.deepClone(this.state);
            return newCollection;
        }
    });

    return PageableCollection;
});
