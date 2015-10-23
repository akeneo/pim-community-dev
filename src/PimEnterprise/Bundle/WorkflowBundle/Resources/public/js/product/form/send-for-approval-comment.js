'use strict';
/**
 * Form to add a comment in a notification when the proposal is sent for approval
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pimee/product-edit-form/abstract-add-notification-comment'
    ],
    function ($, _, Backbone, AbstractCommentForm) {
        return AbstractCommentForm.extend({

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    label: _.__('pimee_enrich.entity.product_draft.modal.title'),
                    characters: _.__('pimee_enrich.entity.product_draft.modal.characters')
                }));

                return this.renderExtensions();
            }
        });
    }
);
