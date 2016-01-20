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

                gridElement.on('click', '.partial-approve-link, .partial-reject-link', function () {
                    var $this  = $(this);
                    var action = $this.data('action');
                    var route  = 'pimee_workflow_product_draft_rest_partial_' + action;
                    var title  = _.__('pimee_workflow.proposal.partial_' + action + '.modal.title');

                    var modal = new FormModal(
                        'pimee-workflow-partial-approve-proposal-comment',
                        function () {
                            return $.Deferred().resolve();
                        },
                        {
                            title: title,
                            cancelText: _.__('pimee_enrich.entity.product_draft.modal.cancel'),
                            okText: _.__('pimee_enrich.entity.product_draft.modal.confirm')
                        }
                    );

                    modal
                        .open()
                        .then(function (myFormData) {

                            $.post(Routing.generate(route, {
                                id: $this.data('product-draft'),
                                code: $this.data('attribute'),
                                scope: _.isEmpty($this.data('scope')) ? null : $this.data('scope'),
                                locale: _.isEmpty($this.data('locale')) ? null : $this.data('locale'),
                                comment: _.isUndefined(myFormData.comment) ? null : myFormData.comment
                            })).then(function () {
                                mediator.trigger('datagrid:doRefresh:' + gridName);
                                messenger.notificationFlashMessage(
                                    'success',
                                    _.__('pimee_workflow.proposal.partial_' + action + '.modal.success')
                                );
                            }).fail(function () {
                                messenger.notificationFlashMessage(
                                    'error',
                                    _.__('pimee_workflow.proposal.partial_' + action + '.modal.error')
                                );
                            });
                        }.bind(this));
                });
            }
        };
    }
);
