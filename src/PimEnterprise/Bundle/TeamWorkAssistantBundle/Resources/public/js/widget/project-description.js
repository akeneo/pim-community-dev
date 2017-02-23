'use strict';

/**
 * Project description.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'backbone',
        'text!team-work-assistant/templates/widget/project-description'
    ],
    function ($, _, __, BaseForm, Backbone, template) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknProjectWidget-resume',

            /**
             * Render the project description from the model
             */
            render: function () {
                this.$el.html(this.template({
                    title: __('team_work_assistant.widget.description'),
                    description: this.getFormData().currentProject.description
                }));
            }
        });
    }
);
