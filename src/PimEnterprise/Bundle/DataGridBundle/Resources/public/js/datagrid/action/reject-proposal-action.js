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
        'pim/form-modal'
    ],
    function (
        $,
        _,
        mediator,
        messenger,
        __,
        AjaxAction,
        FormModal
    ) {
        return AjaxAction.extend({
            /**
             * Parameters to be send with the request
             */
            actionParameters: {},

            /**
             * {@inheritdoc}
             */
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default handler to trigger the popin to add comment
             *
             * {@inheritdoc}
             */
            _handleAjax: function (action) {
                var modalParameters = {
                    title: __('pimee_enrich.entity.product_draft.modal.reject_proposal'),
                    okText: __('pimee_enrich.entity.product_draft.modal.confirm'),
                    cancelText: __('pimee_enrich.entity.product_draft.modal.cancel')
                };

                var formModal = new FormModal(
                    'pimee-workflow-proposal-add-comment',
                    this.validateForm.bind(this),
                    modalParameters
                );

                formModal.open().then(function () {
                    AjaxAction.prototype._handleAjax.apply(this, [action]);
                }.bind(this));
            },

            /**
             * Override the default handler to trigger the event containing the new product data
             *
             * @param product
             */
            _onAjaxSuccess: function (product) {
                messenger.notificationFlashMessage(
                    'success',
                    __('pimee_enrich.entity.product.tab.proposals.messages.reject.success')
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
            validateForm: function (form) {
                var comment = form.getFormData().comment;
                this.actionParameters.comment = _.isUndefined(comment) ? null : comment;

                return $.Deferred().resolve();
            },

            /**
             * {@inheritdoc}
             */
            getActionParameters: function () {
                return this.actionParameters;
            }
        });
    }
);
