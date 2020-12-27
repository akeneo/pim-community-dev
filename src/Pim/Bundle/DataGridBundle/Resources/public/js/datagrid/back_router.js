'use strict';

define(
    ['pim/router', 'underscore', 'backbone'],
    function(router, _, Backbone) {

        var BackRouter = function(options) {
            this.initialize.apply(this, arguments);
        }


        _.extend(BackRouter.prototype, {

            /** @property {string} */
            back_route: null,

            /** @property {Object} */
            back_route_parameters: null,

            /**
             * {@inheritdoc}
             */
            initialize: function(options) {
                if (options.backRoute) {
                    this.back_route = options.backRoute
                }
                if (options.back_route_parameters) {
                    this.back_route_parameters = options.back_route_parameters
                }
            },

            /**
             * Redirect to the configured back route or custom route.
             *
             * @param {string|undefined} route
             * @param {Object|undefined}parameters
             */
            redirectToBackRoute: function (route, parameters) {
                var url = this.getBackUrlWithParameters(route, parameters);
                router.redirect(url);
            },

            /**
             * Get url to redirect back with configured route or custom route.
             *
             * @param {string|undefined} route
             * @param {Object|undefined}parameters
             */
            getBackUrlWithParameters: function (route, parameters) {
                if (_.isUndefined(parameters)) {
                    parameters = {};
                }
                if (_.isUndefined(route)) {
                    route = this.back_route;
                }
                return router.generate(
                    this.back_route,
                    _.extend(
                        this.back_route_parameters,
                        parameters
                    )
                );
            }
        });

        return new BackRouter(__moduleConfig);
    }
);
