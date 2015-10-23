'use strict';

/**
 * Abstract form to add a comment in a notification
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'text!pimee/template/product/meta/notification-comment'
    ],
    function ($, _, Backbone, mediator, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'input textarea': 'updateCounter'
            },

            /**
             * Character limit for the counter
             */
            charLimit: 255,

            /**
             * Initialize the form and the model
             */
            initialize: function () {
                this.model = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Triggered by change and keyup on textarea to update the remaining characters counter
             */
            updateCounter: function () {
                var content        = this.$('textarea').val();
                var remainingChars = this.charLimit - content.length;

                this.$('.remaining-chars').text(remainingChars);
                if (0 > remainingChars) {
                    this.disableOkBtn();
                } else {
                    this.enableOkBtn();
                }

                this.model.set('comment', content);
            },

            /**
             * Enable the modal ok button and change the counter color
             */
            enableOkBtn: function () {
                mediator.trigger('pim_enrich:form:modal:ok_button:enable');
                this.$('.chars-counter').removeClass('under').addClass('above');
            },

            /**
             * Disable the modal ok button and change the counter color
             */
            disableOkBtn: function () {
                mediator.trigger('pim_enrich:form:modal:ok_button:disable');
                this.$('.chars-counter').removeClass('above').addClass('under');
            },

            /**
             * Render the template
             */
            render: function () {
                throw new Error('Must be implemented.');
            }
        });
    }
);
