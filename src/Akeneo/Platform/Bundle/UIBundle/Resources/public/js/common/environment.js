'use strict';

define(
    ['jquery', 'routing'],
    function ($, Routing) {
        var promise = null;

        var loadEnvironment = function () {
            if (null === promise) {
                promise = $.getJSON(Routing.generate('pim_ui_environment_rest_list')).fail(() => {
                    throw Error('It seems that your web server is not well configured as we were not able ' +
                        'to load the frontend configuration. The most likely reason is that the mod_rewrite ' +
                        'module is not installed/enabled.');
                });
            }

            return promise;
        };

        return {
            /**
             * Returns server variables
             *
             * @return {Promise}
             */
            getVariables: function () {
                return loadEnvironment().then(config => config);
            }
        };
    }
);
