define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/user-context',
        'text!activity-manager/templates/widget/project-selector-line'
    ],
    function ($, _, __, Backbone, UserContext, template) {
        'use strict';

        return Backbone.View.extend({
            template: _.template(template),
            project: {},
            type: null,

            /**
             * @param {Object} project
             * @param {String} type
             */
            initialize: function (project, type) {
                this.project = project;
                this.type = type;
            },

            /**
             * Render the project line in select2
             */
            render: function () {
                var uiLocale = UserContext.get('uiLocale');
                var channelLabel = this.project.channel.code;

                if (!_.isUndefined(this.project.channel.labels[uiLocale])) {
                    channelLabel = this.project.channel.labels[uiLocale];
                }

                this.$el.html(this.template({
                    type: this.type,
                    projectLabel: this.project.label,
                    projectChannel: channelLabel,
                    projectLocale: this.project.locale.label
                }));
            }
        });
    }
);
