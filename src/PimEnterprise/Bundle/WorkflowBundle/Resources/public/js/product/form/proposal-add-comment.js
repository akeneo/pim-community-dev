'use strict';

/**
 * Form used to add a comment on a proposal when
 * a product owner refuses it or accepts it.
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'text!pimee/template/product/meta/notification-comment'
    ],
    function ($, _, Backbone, BaseForm, template) {
        return BaseForm.extend({
            /**
             * Template used for rendering the form
             */
            template: _.template(template),

            /**
             * Backbone events we listen to
             */
            events: {
                'change textarea': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.model = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * Update the model the form is attached to
             */
            updateModel: function () {
                this.model.set('comment', this.$('textarea').val());
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(
                    this.template({
                        label: _.__('pimee_enrich.entity.product_draft.modal.title_comment')
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
