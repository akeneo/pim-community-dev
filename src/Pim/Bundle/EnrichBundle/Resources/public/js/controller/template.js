'use strict';

define(
    ['jquery', 'underscore', 'pim/controller/base'],
    function ($, _, BaseController) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route, path) {
                return $.get(path)
                    .then(this.renderTemplate.bind(this))
                    .promise();
            },

            /**
             * Add the given content to the current container
             *
             * @param {String} content
             */
            renderTemplate: function (content) {
                if (!this.active) {
                    return;
                }

                this.$el.html(content);
            }
        });
    }
);
