/* jshint unused:false */
'use strict';

define(['backbone'], function (Backbone) {
    return Backbone.View.extend({
        /**
         * Render the route given in parameter
         *
         * @param {String} route
         * @param {String} path
         *
         * @return {Promise}
         */
        renderRoute: function (route, path) {
            throw new Error('Method renderRoute is abstract and must be implemented!');
        }
    });
});
