'use strict';

/**
 * Form used to add a comment in a notification when the proposal is partial approved
 */
define(
    [
        'underscore',
        'pimee/product-edit-form/abstract-add-notification-comment'
    ],
    function (_, AbstractCommentForm) {
        return AbstractCommentForm.extend({
            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(
                    this.template({
                        label: _.__('pimee_workflow.entity.proposal.modal.title'),
                        characters: _.__('pimee_enrich.entity.product_draft.modal.characters')
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
