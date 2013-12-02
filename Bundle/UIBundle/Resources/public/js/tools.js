/* global define, require*/
define(['underscore'],
function (_) {
    'use strict';

    /**
     * @export oro/tools
     * @name   oro.tools
     */
    return {
        /**
         * Loads dynamic list of modules and execute callback function with passed modules
         *
         * @param {Object.<string, string>} modules where keys are formal module names and values are actual
         * @param {function(Object)} callback
         */
        loadModules: function (modules, callback) {
            var requirements = _.values(modules);
            // load all dependencies and build grid
            require(requirements, function () {
                _.each(modules, _.bind(function(value, key) {
                    modules[key] = this[value];
                }, _.object(requirements, _.toArray(arguments))));
                callback(modules);
            });
        }
    };
});
