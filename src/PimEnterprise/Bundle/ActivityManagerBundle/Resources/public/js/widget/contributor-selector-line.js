define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'text!activity-manager/templates/widget/contributor-selector-line'
    ],
    function ($, _, __, Backbone, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            username: null,
            type: null,

            /**
             * @param {Object} username
             * @param {String} type
             */
            initialize: function (username, type) {
                this.username = username;
                this.type = type;
            },

            /**
             * Render the contributor line in select2
             */
            render: function () {
                this.$el.html(this.template({
                    type: this.type,
                    username: this.username
                }));
            }
        });
    }
);
