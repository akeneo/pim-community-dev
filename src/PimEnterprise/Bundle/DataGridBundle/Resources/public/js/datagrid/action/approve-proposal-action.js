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
        'oro/datagrid/ajax-action',
        'pim/form-modal'
    ],
    function (
        $,
        _,
        mediator,
        AjaxAction,
        FormModal
    ) {
        return AjaxAction.extend({

            actionParameters: {},

            /**
             * @inheritdoc
             */
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default handler to trigger the popin to add comment
             *
             * @param action
             */
            _handleAjax: function (action) {
                var modalParameters = {
                    title: _.__('pimee_enrich.entity.product_draft.modal.accept_approval'),
                    okText: _.__('pimee_enrich.entity.product_draft.modal.confirm'),
                    cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel')
                };

                var formModal = new FormModal(
                    'pimee-proposal-add-comment-form',
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
                this.datagrid.collection.fetch();

                mediator.trigger('pim_enrich:form:proposal:post_approve:success', product);
            },

            /**
             * Override the default handler to avoid displaying the error modal and triggering our own event instead
             *
             * @param jqXHR
             */
            _onAjaxError: function (jqXHR) {
                this.datagrid.hideLoading();

                mediator.trigger('pim_enrich:form:proposal:post_approve:error', jqXHR.responseJSON.message);
            },

            /**
             * Validate the given form data. We must check for comment length.
             *
             * @param {Object }form
             *
             * @return {Deferred}
             */
            validateForm: function (form) {
                var deferred = $.Deferred();
                var comment = form.getFormData().comment;
                this.actionParameters.comment = comment;

                // TODO: Check for max char. length
                deferred.resolve();

                return deferred;
            },

            /**
             * @inheritdoc
             */
            getActionParameters: function () {
                return this.actionParameters;
            }
        });
    }
);
