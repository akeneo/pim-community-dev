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
        'pimee/product-edit-form/abstract-add-notification-comment'
    ],
    function ($,_, Backbone, AbstractCommentForm) {
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
