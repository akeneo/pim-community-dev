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
                    var documentType = $this.data('document-type');
                    var route = 'pimee_workflow_' + documentType + '_rest_partial_' + action;
                    var title  = _.__('pimee_workflow.proposal.partial_' + action + '.modal.title');

                    var modal = new FormModal(
                        'pimee-workflow-partial-approve-proposal-comment',
                        function () {
                            return $.Deferred().resolve();
                        },
                        {
                            title: title,
                            cancelText: _.__('pim_common.cancel'),
                            okText: _.__('pimee_enrich.entity.product_draft.module.proposal.confirm'),
                            illustrationClass: 'proposal'
                        }
                    );

                    modal
                        .open()
                        .then(function (myFormData) {

                            $.post(Routing.generate(route, {
                                id: $this.data('draft'),
                                code: $this.data('attribute'),
                                scope: _.isEmpty($this.data('scope')) ? null : $this.data('scope'),
                                locale: _.isEmpty($this.data('locale')) ? null : $this.data('locale'),
                                comment: _.isUndefined(myFormData.comment) ? null : myFormData.comment
                            })).then(function () {
                                messenger.notify(
                                    'success',
                                    _.__('pimee_workflow.proposal.partial_' + action + '.modal.success')
                                );

                                /**
                                 * Hard reload of the page, if deleted the last grid proposal,
                                 * in order to refresh proposal grid filters.
                                 */
                                if (1 === $('table.proposal-changes').length) {
                                    window.location.reload();
                                } else {
                                    mediator.trigger('datagrid:doRefresh:' + gridName);
                                }
                            }).fail(function () {
                                messenger.notify(
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
