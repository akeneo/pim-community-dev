'use strict';

/**
 * Remove proposal action
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(['jquery', 'underscore', 'oro/mediator', 'oro/datagrid/ajax-action', 'pim/form-modal', 'routing'], function(
  $,
  _,
  mediator,
  AjaxAction,
  FormModal,
  Router
) {
  return AjaxAction.extend({
    /**
     * Parameters to be send with the request
     */
    actionParameters: {},

    /**
     * {@inheritdoc}
     */
    getMethod() {
      return 'POST';
    },

    /**
     * {@inheritdoc}
     */
    getLink() {
      const productDraftType = this.model.get('document_type');
      const id = this.model.get('proposal_id');

      return Router.generate('pimee_workflow_' + productDraftType + '_rest_remove', {id});
    },

    /**
     * Override the default handler to trigger the popin to add comment
     *
     * {@inheritdoc}
     */
    _handleAjax(action) {
      var modalParameters = {
        title: _.__('pimee_enrich.entity.product_draft.module.proposal.remove'),
        okText: _.__('pimee_enrich.entity.product_draft.module.proposal.confirm'),
        cancelText: _.__('pim_common.cancel'),
        illustrationClass: 'proposal',
      };

      var formModal = new FormModal(
        'pimee-workflow-proposal-add-comment',
        this.validateForm.bind(this),
        modalParameters
      );

      formModal.open().then(
        function() {
          AjaxAction.prototype._handleAjax.apply(this, [action]);
        }.bind(this)
      );
    },

    /**
     * Override the default handler to trigger the event containing the new product data
     *
     * @param product
     */
    _onAjaxSuccess(product) {
      this.datagrid.collection.fetch();

      mediator.trigger('pim_enrich:form:proposal:post_remove:success', product);
    },

    /**
     * Validate the given form data. We must check for comment length.
     *
     * @param {Object} form
     *
     * @return {Promise}
     */
    validateForm(form) {
      var comment = form.getFormData().comment;
      this.actionParameters.comment = _.isUndefined(comment) ? null : comment;

      return $.Deferred().resolve();
    },

    /**
     * {@inheritdoc}
     */
    getActionParameters() {
      return this.actionParameters;
    },
  });
});
