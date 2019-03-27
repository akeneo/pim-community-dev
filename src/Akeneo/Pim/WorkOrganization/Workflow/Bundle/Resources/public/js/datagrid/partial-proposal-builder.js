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

                gridElement.on('click', '.partial-approve-link, .partial-reject-link', function (event) {
                    const $this  = event.currentTarget;
                    const action = $this.dataset.action;
                    const author = $this.dataset.author;
                    const documentType = $this.dataset.documentType;
                    const route = 'pimee_workflow_' + documentType + '_rest_partial_' + action;
                    const title  = _.__('pimee_workflow.proposal.partial_' + action + '.modal.title');

                    const id = $this.dataset.draft;
                    const code = $this.dataset.attribute;
                    const scope = _.isEmpty($this.dataset.scope) ? null : $this.dataset.scope;
                    const locale = _.isEmpty($this.dataset.locale) ? null : $this.dataset.locale;

                    if ('Franklin' !== author) {
                        const modal = new FormModal(
                            'pimee-workflow-partial-approve-proposal-comment',
                            function () {
                                return $.Deferred().resolve();
                            },
                            {
                                title: title,
                                cancelText: _.__('pim_common.cancel'),
                                okText: _.__('pimee_enrich.entity.product.module.approval.send')
                            }
                        );

                        modal
                            .open()
                            .then((myFormData) => {
                                const routing = Routing.generate(route, {
                                    id: id,
                                    code: code,
                                    scope: scope,
                                    locale: locale,
                                    comment: _.isUndefined(myFormData.comment) ? null : myFormData.comment
                                });

                                this.doAction(action, routing, gridName);
                            });
                    } else {
                        const routing = Routing.generate(route, {
                            id: id,
                            code: code,
                            scope: scope,
                            locale: locale,
                            comment: null
                        });
                        this.doAction(action, routing, gridName);
                    }
                }.bind(this));
            },

            doAction: function (action, routing, gridName) {
                return $.post(routing).then(() => {
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
                }).fail(() => {
                    messenger.notify(
                        'error',
                        _.__('pimee_workflow.proposal.partial_' + action + '.modal.error')
                    );
                });
            }
        };
    }
);
