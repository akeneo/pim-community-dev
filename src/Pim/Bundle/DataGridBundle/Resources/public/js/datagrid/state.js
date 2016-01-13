define(
    ['underscore', 'oro/mediator'],
    function(_, mediator) {
        'use strict';

        var storageEnabled = typeof Storage !== 'undefined' && sessionStorage;

        var _get = function (alias, key) {
            if (storageEnabled) {
                return sessionStorage.getItem(alias + '.' + key);
            }
        };

        var _getParsed = function(alias, key) {
            var rawValue = _get(alias, key);
            var parsedValue = {};

            if (null == rawValue) {
                return rawValue;
            }
            rawValue.split("&").forEach(function(part) {
                if (!part) return;
                var item = part.split("=");
                var key = decodeURIComponent(item[0]);
                var from = key.indexOf("[");
                if (from==-1) {
                    parsedValue[key] = decodeURIComponent(item[1]);
                } else {
                    var to = key.indexOf("]");
                    var index = key.substring(from+1,to);
                    key = key.substring(0,from);
                    if (!parsedValue[key]) {
                        parsedValue[key] = [];
                    }
                    if (!index) {
                        parsedValue[key].push(item[1]);
                    } else {
                        parsedValue[key][index] = item[1];
                    }
                }
            });

            return parsedValue;
        };

        var _set = function (alias, key, value) {
            if (storageEnabled) {
                var oldValue = _get(alias, key);
                if (oldValue !== value) {
                    sessionStorage.setItem(alias + '.' + key, value);
                    if (oldValue === null) {
                        mediator.trigger('grid:' + alias + ':state_set', { 'item': key, 'newValue': value });
                    } else {
                        mediator.trigger('grid:' + alias + ':state_changed', { 'item': key, 'oldValue': oldValue, 'newValue': value });
                    }
                }
            }
        };

        var _remove = function (alias, key) {
            if (storageEnabled) {
                var value = _get(alias, key);
                if (value !== null) {
                    sessionStorage.removeItem(alias + '.' + key);
                    mediator.trigger('grid:' + alias + ':state_reset', { 'item': key, 'oldValue': value });
                }
            }
        };

        /**
         * A wrapper for storing datagrid state
         */
        return {
            get: function(alias, keys) {
                if (_.isArray(keys)) {
                    var values = {};
                    _.each(keys, function(key) {
                        values[key] = _get(alias, key);
                    });

                    return values;
                } else {
                    return _get.apply(this, arguments);
                }
            },
            getParsed: function(alias, keys) {
                if (_.isArray(keys)) {
                    var values = {};
                    _.each(keys, function(key) {
                        values[key] = _getParsed(alias, key);
                    });

                    return values;
                } else {
                    return _getParsed.apply(this, arguments);
                }
            },
            set: function(alias, data) {
                if (_.isObject(data)) {
                    _.each(data, function(key, value) {
                        _set(alias, value, key);
                    });
                } else {
                    _set.apply(this, arguments);
                }

                return this;
            },
            remove: function(alias, keys) {
                if (_.isArray(keys)) {
                    _.each(keys, function(key) {
                        _remove(alias, key);
                    });
                } else {
                    _remove.apply(this, arguments);
                }

                return this;
            },
            refreshFiltersFromUrl: function (alias) {
                var storageHash = this.get(alias, 'filters');
                var hash        = location.hash;

                if (-1 === hash.indexOf('|g/')) {
                    return;
                }

                var urlHash = hash.split('|g/').pop();

                if (!storageHash) {
                    this.set(alias, {'filters': urlHash});

                    return;
                }

                var storageFilters = storageHash.split('&');
                var urlFilters     = urlHash.split('&');

                if (!_.isEqual(urlFilters.sort(), storageFilters.sort())) {
                    this.set(alias, {'filters': urlHash});
                }
            }
        };
    }
);
