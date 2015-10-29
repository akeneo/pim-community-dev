'use strict';

define(
    ['jquery', 'underscore', 'pim/controller/base'],
    function ($, _, BaseController) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route, path) {
                return $.get(path).then(_.bind(this.renderTemplate, this)).promise();
            },
            /**
             * Add the given template to the current container
             *
             * @param {String} template
             */
            renderTemplate: function (template) {
                this.$el.html(template);
            }
        });
    }
);
