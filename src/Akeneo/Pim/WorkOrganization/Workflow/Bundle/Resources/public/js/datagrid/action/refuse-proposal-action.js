'use strict';

/**
 * Reject proposal action
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'oro/messenger',
        'oro/translator',
        'oro/datagrid/ajax-action',
        'pim/form-modal',
        'routing'
    ],
    function (
        $,
        _,
        mediator,
        messenger,
        __,
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

                return Router.generate('pimee_workflow_' + productDraftType + '_rest_refuse', { id });
            },

            /**
             * Override the default handler to trigger the popin to add comment
             *
             * {@inheritdoc}
             */
            _handleAjax(action) {
                if (this._isAllowedToComment(action)) {
                    const modalParameters = {
                        title: __('pimee_enrich.entity.product_draft.module.proposal.reject'),
                        okText: __('pimee_enrich.entity.product.module.approval.send'),
                        cancelText: __('pim_common.cancel')
                    };

                    const formModal = new FormModal(
                        'pimee-workflow-proposal-add-comment',
                        this.validateForm.bind(this),
                        modalParameters
                    );

                    formModal.open().then(function () {
                        AjaxAction.prototype._handleAjax.apply(this, [action]);
                    }.bind(this));
                } else {
                    AjaxAction.prototype._handleAjax.apply(this, [action]);
                }
            },

            _isAllowedToComment(action) {
                return 'Franklin' !== action.model.attributes.author;
            },

            /**
             * Override the default handler to trigger the event containing the new product data
             *
             * @param product
             */
            _onAjaxSuccess(product) {
                messenger.notify(
                    'success',
                    __('pimee_enrich.entity.product_draft.flash.reject.success')
                );

                this.datagrid.collection.fetch();

                mediator.trigger('pim_enrich:form:proposal:post_reject:success', product);
            },

            /**
             * Validate the given form data. We must check for comment length.
             *
             * @param {Object }form
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
            }
        });
    }
);
