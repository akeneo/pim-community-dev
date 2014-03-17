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
