'use strict';
/**
 * Form to add a comment in a notification when the proposal is sent for approval
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'backbone',
  'pimee/product-edit-form/abstract-add-notification-comment',
], function($, _, __, Backbone, AbstractCommentForm) {
  return AbstractCommentForm.extend({
    /**
     * {@inheritdoc}
     */
    render: function() {
      this.$el.html(
        this.template({
          label: __('pimee_workflow.entity.proposal.modal.title'),
          characters: __('pimee_enrich.entity.product_draft.module.proposal.comment_chars'),
        })
      );
    },
  });
});
