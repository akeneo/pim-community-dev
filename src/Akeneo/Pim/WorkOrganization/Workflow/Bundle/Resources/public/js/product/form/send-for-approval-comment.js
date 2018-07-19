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
                this.$el.html(this.template({
                    subTitleLabel: __('pim_enrich.entity.product.plural_label'),
                    titleLabel: __('pimee_enrich.entity.product.module.approval.send'),
                    label: __('pimee_enrich.entity.product.module.approval.comment_title'),
                    characters: __('pimee_enrich.entity.product.module.approval.comment_chars')
                }));

                return this.renderExtensions();
            }
        });
    }
);
