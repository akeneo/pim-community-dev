'use strict';

define(function (require) {
    var BaseController = require('pim/controller/base');
    var $ = require('jquery');
    var _ = require('underscore');

    return BaseController.extend({
        renderRoute: function (route, path) {
            return $.get(path).then(_.bind(this.renderTemplate, this)).promise();
        },
        renderTemplate: function (template) {
            this.$el.html(template);
        }
    });
});
