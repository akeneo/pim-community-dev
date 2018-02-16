'use strict';

/**
 * Mass approve proposal action.
 * It displays a popin to allow the user to set a comment for this approval.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'routing',
        'oro/translator',
        'oro/datagrid/mass-action',
        'oro/navigation',
        'oro/messenger',
        'pim/form-modal'
    ],
    function (
        $,
        _,
        Routing,
        __,
        MassAction,
        Navigation,
        messenger,
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
                    title: __('pimee_enrich.entity.product_draft.modal.accept_selected_proposal'),
                    okText: __('pimee_enrich.entity.product_draft.modal.confirm'),
                    cancelText: __('pimee_enrich.entity.product_draft.modal.cancel')
                };

                var formModal = new FormModal(
                    'pimee-workflow-proposal-add-comment',
                    this.validateForm.bind(this),
                    modalParameters
                );

                formModal.open().then(function () {
                    $.post(this.getLinkWithParameters(), {itemIds: this.getSelectedRows().join(',')})
                        .done(function (data) {
                            var navigation = Navigation.getInstance();
                            var url = Routing.generate(
                                'pimee_workflow_product_draft_mass_action_redirect',
                                {'jobExecutionId': data.jobExecutionId}
                            );

                            if (navigation) {
                                navigation.processRedirect({fullRedirect: false, location: url});
                            }
                        })
                        .error(function (jqXHR) {
                            messenger.notificationFlashMessage(
                                'error',
                                __(jqXHR.responseText)
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
