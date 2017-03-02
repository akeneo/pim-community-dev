'use strict';

/**
 * Approve proposal action
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
                    title: __('pimee_enrich.entity.product_draft.modal.accept_proposal'),
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
             * @param response
             */
            _onAjaxSuccess: function (response) {
                messenger.notificationFlashMessage(
                    'success',
                    __('pimee_enrich.entity.product.tab.proposals.messages.approve.success')
                );

                mediator.trigger('pim_enrich:form:proposal:post_approve:success', response);

                /**
                 * Hard reload of the page, if deleted the last grid proposal,
                 * in order to refresh proposal grid filters.
                 */
                if (1 === this.datagrid.collection.models.length && 'proposal-grid' === this.datagrid.name) {
                    window.location.reload();
                } else {
                    this.datagrid.collection.fetch();
                }
            },

            /**
             * Override the default handler to avoid displaying the error modal and triggering our own event instead
             *
             * @param jqXHR
             */
            _onAjaxError: function (jqXHR) {
                var message = jqXHR.responseJSON.message;

                messenger.notificationFlashMessage(
                    'error',
                    __('pimee_enrich.entity.product.tab.proposals.messages.approve.error', {
                        error: jqXHR.responseJSON.message
                    })
                );

                this.datagrid.hideLoading();

                mediator.trigger('pim_enrich:form:proposal:post_approve:error', message);
            },

            /**
             * Validate the given form data. We must check for comment length.
             *
             * @param {Object} form
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
