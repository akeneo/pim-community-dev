'use strict';

/**
 * Mass refuse proposal action.
 * It displays a popin to allow the user to set a comment for this refusal.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/datagrid/mass-action',
        'pim/form-modal'
    ],
    function(
        $,
        _,
        MassAction,
        FormModal
    ) {
        return MassAction.extend({
            /**
             * The comment the user typed in the modal
             */
            comment: null,

            /**
             * @inheritdoc
             */
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default execute to trigger the popin to add comment
             */
            execute: function() {
                var modalParameters = {
                    title: _.__('pimee_enrich.entity.product_draft.modal.reject_selected_proposal'),
                    okText: _.__('pimee_enrich.entity.product_draft.modal.confirm'),
                    cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel')
                };

                var formModal = new FormModal(
                    'pimee-proposal-add-comment-form',
                    this.validateForm.bind(this),
                    modalParameters
                );

                formModal.open().then(function () {
                    MassAction.prototype.execute.apply(this, arguments);
                }.bind(this));
            },

            /**
             * Validate the given form data. We must check for comment length.
             *
             * @param {Object} form
             *
             * @return {Deferred}
             */
            validateForm: function (form) {
                var deferred = $.Deferred();
                var comment = form.getFormData().comment;

                this.comment = _.isUndefined(comment) ? null : comment;

                // TODO: Check for max char. length
                deferred.resolve();

                return deferred;
            },

            /**
             * @inheritdoc
             */
            getActionParameters: function() {
                var massActionParam = MassAction.prototype.getActionParameters.apply(this, arguments);

                return _.extend(massActionParam, {'comment': this.comment});
            }
        });
    }
);
