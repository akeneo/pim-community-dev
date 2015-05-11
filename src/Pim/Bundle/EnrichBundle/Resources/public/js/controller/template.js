'use strict';

define(function (require) {
    var BaseController = require('pim/controller/base');
    var $ = require('jquery');
    var _ = require('underscore');

    return BaseController.extend({
        renderRoute: function (route, path) {
            return $.get(path).then(_.bind(function (template) {
                this.$el.html(template);
            }, this)).promise();
        }
    });
});
