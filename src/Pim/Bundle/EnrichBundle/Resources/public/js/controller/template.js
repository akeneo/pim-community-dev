'use strict';

define(
    ['jquery', 'underscore', 'pim/controller/base'],
    function ($, _, BaseController) {
        return BaseController.extend({
            renderRoute: function (route, path) {
                return $.get(path).then(_.bind(this.renderTemplate, this)).promise();
            },
            renderTemplate: function (template) {
                this.$el.html(template);
            }
        });
    }
);
