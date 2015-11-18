'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form-modal',
        'routing',
        'oro/messenger',
        'oro/mediator'
    ],
    function (
        $,
        _,
        FormModal,
        Routing,
        messenger,
        mediator
    ) {
        return {
            /**
             * Callback triggered after the grid is loaded that add partial approve behavior
             *
             * @param {Object} gridElement
             * @param {string} gridName
             *
             * @return {Object}
             */
            init: function (gridElement, gridName) {
                var modal = new FormModal(
                    'pimee-workflow-partial-approve-proposal-comment',
                    function () {
                        return $.Deferred().resolve();
                    },
                    {
                        title: _.__('pimee_workflow.proposal.partial_accept.modal.title'),
                        cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel'),
                        okText: _.__('pimee_enrich.entity.product_draft.modal.confirm')
                    }
                );

                gridElement.on('click', '.partial-approve-link', function (event) {
                    event.preventDefault();

                    modal
                        .open()
                        .then(function (myFormData) {
                            var $this = $(this);
                            $.post(Routing.generate('pimee_workflow_product_draft_rest_partial_approve', {
                                id: $this.data('product-draft'),
                                code: $this.data('attribute'),
                                scope: _.isEmpty($this.data('scope')) ? null : $this.data('scope'),
                                locale: _.isEmpty($this.data('locale')) ? null : $this.data('locale'),
                                comment: _.isUndefined(myFormData.comment) ? null : myFormData.comment
                            })).then(function () {
                                mediator.trigger('datagrid:doRefresh:' + gridName);
                                messenger.notificationFlashMessage(
                                    'success',
                                    _.__('pimee_workflow.proposal.partial_accept.modal.success')
                                );
                            }).fail(function () {
                                messenger.notificationFlashMessage(
                                    'error',
                                    _.__('pimee_workflow.proposal.partial_accept.modal.error')
                                );
                            });
                        }.bind(this));
                });
            }
        };
    }
);
