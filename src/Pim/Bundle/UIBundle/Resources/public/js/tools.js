define(['underscore', 'paths'],
function (_, paths) {
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
         * @param {function (Object)} callback
         */
        loadModules: function (modules, callback) {
            var arrayArguments = _.object(requirements,  arguments)
            var requirements = _.values(modules);
            var requestFetcher = require.context('./src/Pim/Bundle', true, /^\.\/.*\.js$/)

            require.ensure([], function () {
                _.each(modules, _.bind(function (value, key) {
                    var module = requestFetcher(paths[value])
                    modules[key] = module
                }, arrayArguments));
                callback(modules);
            });
        }
    };
});
