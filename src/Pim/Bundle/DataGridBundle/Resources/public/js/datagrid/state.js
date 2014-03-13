define(
    ['underscore', 'oro/mediator', 'oro/pageable-collection'],
    function(_, mediator, PageableCollection) {
        'use strict';

        var storageEnabled = typeof Storage !== 'undefined' && sessionStorage;

        var _set = function (alias, key, value) {
            if (storageEnabled) {
                sessionStorage.setItem(alias + '.' + key, value);
            }
        };

        var _get = function (alias, key) {
            if (storageEnabled) {
                return sessionStorage.getItem(alias + '.' + key);
            }
        };

        var _remove = function (alias, key) {
            if (storageEnabled) {
                sessionStorage.removeItem(alias + '.' + key);
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
            }
        }
    }
);
