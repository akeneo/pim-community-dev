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
                        subTitleLabel: __('pim_enrich.entity.product.plural_label'),
                        titleLabel: __('pimee_enrich.entity.product.module.approval.send'),
                        label: __('pimee_workflow.entity.proposal.modal.title'),
                        characters: __('pimee_enrich.entity.product.module.approval.comment_chars')
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
