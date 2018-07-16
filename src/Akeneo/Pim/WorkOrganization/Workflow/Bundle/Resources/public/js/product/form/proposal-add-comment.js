'use strict';

/**
 * Form used to add a comment on a proposal when
 * a product owner refuses it or accepts it.
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
                    this.template({
                        subTitleLabel: __('pim_enrich.entity.product.plural_label'),
                        titleLabel: __('pimee_enrich.entity.product.module.approval.send'),
                        label:__('pimee_workflow.entity.proposal.modal.title'),
                        characters: __('pimee_enrich.entity.product.module.approval.comment_chars')
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
