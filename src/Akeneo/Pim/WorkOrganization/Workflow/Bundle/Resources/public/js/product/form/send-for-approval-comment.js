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
        'oro/translator',
        'backbone',
        'pimee/product-edit-form/abstract-add-notification-comment'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        AbstractCommentForm
    ) {
        return AbstractCommentForm.extend({
            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(
                    this.modalTemplate({
                        title: __('pimee_enrich.entity.product.module.approval.send'),
                        subtitle: __('pim_enrich.entity.product.plural_label'),
                        picture: 'illustration-attribute.svg',
                        okText: __('pimee_enrich.entity.product_draft.module.proposal.confirm'),
                        cancelText: __('pim_common.cancel'),
                        content: this.template({
                            label: __('pimee_enrich.entity.product_draft.module.proposal.comment_title'),
                            characters: __('pimee_enrich.entity.product_draft.module.proposal.comment_chars')
                        })
                    })
                );
            }
        });
    }
);
