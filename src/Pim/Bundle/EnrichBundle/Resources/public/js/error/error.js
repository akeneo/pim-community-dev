'use strict';

define(
    ['underscore', 'backbone', 'pim/template/error/error'],
    function (_, Backbone, template) {
        return Backbone.View.extend({
            template: _.template(template),
            initialize: function (message, statusCode) {
                this.message    = message;
                this.statusCode = statusCode;
            },
            render: function () {
                this.$el.html(this.template({
                    message: this.message,
                    statusCode: this.statusCode
                }));
            }
        });
    }
);
