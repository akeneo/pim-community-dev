'use strict';

/**
 * Form used to add a comment in a notification when the proposal is partial approved
 */
define(
    [
        'underscore',
        'oro/translator',
        'pimee/product-edit-form/abstract-add-notification-comment'
    ],
    function (
        _,
        __,
        AbstractCommentForm
    ) {
        return AbstractCommentForm.extend({
            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(
                    this.template({
                        subTitleLabel: __('pim_menu.item.product'),
                        titleLabel: __('pimee_enrich.entity.product_draft.modal.send_for_approval'),
                        label: __('pimee_workflow.entity.proposal.modal.title'),
                        characters: __('pimee_enrich.entity.product_draft.modal.characters')
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
