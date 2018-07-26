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
        'teamwork-assistant/templates/widget/project-description'
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
                    description: this.getFormData().currentProject.description
                }));
            }
        });
    }
);
