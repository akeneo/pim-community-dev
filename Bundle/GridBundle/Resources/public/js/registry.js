/* global define */
define(['underscore'],
function(_) {
    'use strict';

        /** @property {Object} */
    var storage = {},
        /** @property {boolean} */
        frozen = false;

    /**
     * Global registry implementation
     *
     * @export oro/grid/registry
     * @name   oro/grid.registry
     */
    return {
        /**
         * Returns element stored under specified keys
         *
         * Example: lets assume that storage = {a: {f: 1, s: 2}, b : {t: 3, f: 4}}, then
         * oro.registry.getElement('a')      will return {f: 1, s: 2}
         * oro.registry.getElement('b', 't') will return 3
         * oro.registry.getElement('c')      will return undefined
         *
         * @return {*}
         */
        getElement: function() {
            if (arguments.length == 0) {
                throw new Error('Cannot get element without a key');
            }

            var currentPoint = storage;
            _.each(arguments, function(value) {
                if (_.isObject(currentPoint)) {
                    if (!_.has(currentPoint, value)) {
                        currentPoint = undefined;
                    } else {
                        currentPoint = currentPoint[value];
                    }
                }
            }, this);

            return currentPoint;
        },

        /**
         * Set elements to registry storage under specified keys
         *
         * Example: lets assume that storage = {}, then
         * oro.registry.setElement('a', 1)      will set storage to {a: 1}
         * oro.registry.setElement('b', 's', 2) will set storage to {a: 1, b: {s: 2}}
         */
        setElement: function() {
            if (frozen) {
                throw new Error('Cannot set element into a frozen registry');
            }

            if (arguments.length <= 1) {
                throw new Error('Cannot set element without a key');
            }

            var argumentsData = _.toArray(arguments);
            var keys = argumentsData.slice(0, argumentsData.length - 1);
            var data = argumentsData[arguments.length - 1];

            var currentPoint = storage;
            _.each(keys, function(key, number) {
                // if last key
                if (number == keys.length - 1) {
                    currentPoint[key] = data;
                } else if (!_.has(currentPoint, key)) {
                    currentPoint[key] = {};
                } else if (!_.isObject(currentPoint[key])) {
                    throw new Error('Element with key "' + key + '" is not an object');
                }
                currentPoint = currentPoint[key];
            }, this);
        },

        /**
         * Freeze registry, so it can't be modified any more
         */
        freeze: function() {
            frozen = true;
        }
    };
});
