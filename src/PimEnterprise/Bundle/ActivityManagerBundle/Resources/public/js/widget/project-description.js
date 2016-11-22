define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'text!activity-manager/templates/widget/project-description'
    ],
    function ($, _, __, Backbone, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            description: null,
            className: 'AknProjectWidget-resume',

            /**
             * @param {String} description
             */
            initialize: function (description) {
                this.description = description;
            },

            /**
             * Render the project description
             */
            render: function () {
                this.$el.html(this.template({
                    title: __('activity_manager.widget.description'),
                    description: this.description
                }));
            }
        });
    }
);
