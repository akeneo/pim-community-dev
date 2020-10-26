'use strict';
/**
 * Draft extension
 *
 * @author Filips Alpe <filips@akeneo.com>
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define([
  'jquery',
  'underscore',
  'backbone',
  'routing',
  'oro/messenger',
  'oro/translator',
  'pim/form',
  'pimee/template/product/submit-draft',
  'pimee/template/product/submit-draft-sequential-edit',
  'pim/form-modal',
], function($, _, Backbone, Routing, messenger, __, BaseForm, submitTemplate, submitSequentialEditTemplate, FormModal) {
  const DraftStatus = {IN_PROGRESS: 0};

  return BaseForm.extend({
    className: 'AknButtonList-item',
    submitTemplate: _.template(submitTemplate),
    submitSequentialEditTemplate: _.template(submitSequentialEditTemplate),
    confirmationMessage: __('pimee_enrich.entity.product_draft.module.edit.discard_changes'),
    confirmationTitle: __('pimee_enrich.entity.product_draft.module.edit.discard_changes_title'),
    events: {
      'click .submit-draft': 'onSubmitDraft',
      'click .submit-draft-and-continue': 'onSubmitDraftAndContinue',
    },
    sequentialEdit: undefined,

    /**
     * {@inheritdoc}
     */
    initialize: function(meta) {
      this.config = _.extend({}, meta.config);
    },

    /**
     * Configure this extension
     *
     * @returns {Promise}
     */
    configure: function() {
      this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
      this.listenTo(this.getRoot(), 'pim_enrich:form:sequential-edit', ({isLast}) => {
        this.sequentialEdit = {isLast};
        this.render();
      });

      return BaseForm.prototype.configure.apply(this, arguments);
    },

    /**
     * Return the current productId
     *
     * @return {number}
     */
    getProductId: function() {
      return this.getFormData().meta.id;
    },

    /**
     * Return the current draft status
     * Return null if there is no draft (not created yet or the user owns the product)
     *
     * @returns {number|null}
     */
    getDraftStatus: function() {
      return this.getFormData().meta.draft_status;
    },

    /**
     * Return true if the user owns the product
     *
     * @returns {boolean}
     */
    isOwner: function() {
      return this.getFormData().meta.is_owner;
    },

    /**
     * Refresh the "send for approval" button rendering
     *
     * @returns {Object}
     */
    render: function() {
      if (this.isOwner()) {
        return this;
      }

      if (undefined !== this.sequentialEdit) {
        this.$el.html(this.submitSequentialEditTemplate({__, isLast: this.sequentialEdit.isLast}));
      } else {
        this.$el.html(this.submitTemplate({__}));
      }
      this.delegateEvents();

      return this;
    },

    /**
     * Callback triggered on "send for approval" button click
     *
     * @return {Promise<void>}
     */
    onSubmitDraft: function() {
      const submit = this.parent.getExtension('save').save({silent: true, notifyOnSuccess: false});

      if (
        DraftStatus.IN_PROGRESS !== this.getDraftStatus() &&
        false === this.parent.getExtension('state').hasModelChanged()
      ) {
        submit.then(() => messenger.notify('warning', __('pimee_enrich.entity.product_draft.flash.create.skip')));

        return Promise.resolve();
      }

      return new Promise(resolve =>
        submit
          .then(() => this.createCommentFormModal().open())
          .then(myFormData => {
            const comment = _.isUndefined(myFormData.comment) ? null : myFormData.comment;

            this.getRoot().trigger('pim_enrich:form:state:confirm', {
              message: this.confirmationMessage,
              title: this.confirmationTitle,
              action: () => this.submitDraft(comment).then(() => resolve()),
            });
          })
      );
    },

    /**
     * @return {void}
     */
    onSubmitDraftAndContinue: function() {
      this.onSubmitDraft().then(() => this.parent.getExtension('sequential-edit').continue());
    },

    /**
     * @return {FormModal}
     */
    createCommentFormModal: function() {
      const callback = function() {
        const deferred = $.Deferred();

        deferred.resolve();

        return deferred;
      };

      return new FormModal('pimee-workflow-send-for-approval-comment', callback, {
        title: __('pimee_enrich.entity.product.module.approval.send'),
        subtitle: __('pim_enrich.entity.product.plural_label'),
        picture: 'illustrations/Attribute.svg',
        okText: __('pimee_enrich.entity.product_draft.module.proposal.confirm'),
        cancelText: __('pim_common.cancel'),
        content: '',
      });
    },

    /**
     * Submit the current draft to backend for approval
     *
     * @return {Promise<void>}
     */
    submitDraft: function(comment) {
      const postData = {
        comment: comment,
      };

      postData[this.config.idKeyName] = this.getProductId();

      return $.post(Routing.generate(this.config.routes.ready, postData))
        .then(
          function(product) {
            this.setData(product);

            this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);

            messenger.notify('success', __('pimee_enrich.entity.product_draft.flash.create.success'));
          }.bind(this)
        )
        .fail(function() {
          messenger.notify('error', __('pimee_enrich.entity.product_draft.flash.create.fail'));
        });
    },
  });
});
