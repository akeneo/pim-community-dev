'use strict';

define(function (require) {
    var Backbone = require('backbone');

    return Backbone.View.extend({
        renderRoute: function (route, path) {
            throw new Error('Method renderRoute is abstract and must be implemented!');
        }
    });
});
