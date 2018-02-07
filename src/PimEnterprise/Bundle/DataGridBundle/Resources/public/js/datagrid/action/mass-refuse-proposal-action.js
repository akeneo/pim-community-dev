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
        'oro/navigation',
        'pim/form-modal'
    ],
    function (
        $,
        _,
        MassAction,
        Navigation,
        FormModal
    ) {
        return MassAction.extend({
            /**
             * The comment the user typed in the modal
             */
            comment: null,

            /**
             * {@inheritdoc}
             */
            getMethod: function () {
                return 'POST';
            },

            /**
             * Override the default execute to trigger the popin to add comment
             */
            execute: function () {
                var modalParameters = {
                    title: _.__('pimee_enrich.entity.product_draft.modal.reject_selected_proposal'),
                    okText: _.__('pimee_enrich.entity.product_draft.modal.confirm'),
                    cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel')
                };

                var formModal = new FormModal(
                    'pimee-workflow-proposal-add-comment',
                    this.validateForm.bind(this),
                    modalParameters
                );

                formModal.open().then(function () {
                    MassAction.prototype.execute.apply(this, arguments);
                    $.post(this.getLinkWithParameters(), {itemIds: this.getSelectedRows().join(',')})
                        .done(function (data) {
                            var url = '/product_draft/redirect?jobExecutionId=' + data.jobExecutionId,
                                navigation = Navigation.getInstance();

                            if (navigation) {
                                navigation.processRedirect({fullRedirect: false, location: url});
                            }
                        })
                        .error(function (jqXHR) {
                            messenger.notificationFlashMessage(
                                'error',
                                _.__(jqXHR.responseText)
                            );
                        });
                }.bind(this));
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
                this.comment = _.isUndefined(comment) ? null : comment;

                return $.Deferred().resolve();
            },

            /**
             * {@inheritdoc}
             */
            getActionParameters: function () {
                var massActionParam = MassAction.prototype.getActionParameters.apply(this, arguments);

                return _.extend(massActionParam, {'comment': this.comment});
            }
        });
    }
);
