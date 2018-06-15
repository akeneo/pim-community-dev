/* global define */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'oro/datagrid/mass-action',
        'pim/router',
        'oro/messenger',
        'pim/provider/sequential-edit-provider',
        'oro/loading-mask'
    ],
    function($, _, __, Routing, MassAction, router, messenger, sequentialEditProvider, LoadingMask) {
        'use strict';

        /**
         * Sequential edit action
         *
         * @export  oro/datagrid/sequential-edit-action
         * @class   oro.datagrid.SequentialEditAction
         * @extends oro.datagrid.MassAction
         */
        return MassAction.extend({
            /**
             * Execute sequential edit
             */
            execute: function() {
                const params = Object.assign({}, this.getActionParameters(), {gridName: this.datagrid.name, actionName: 'sequential_edit'});

                const loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('.hash-loading-mask')).show();

                return $.ajax({
                    url: Routing.generate('pim_enrich_sequential_edit_rest_get_ids'),
                    data: params
                }).then((response) => {
                    sequentialEditProvider.set(response.entities);

                    if (1000 < response.total) {
                        messenger.notify(
                            'warning',
                            __('pim_enrich.entity.product.module.sequential_edit.item_limit', {'count': response.total})
                        );
                    }

                    if (0 === response.total) {
                        messenger.notify(
                            'error',
                            __('pim_enrich.entity.product.module.sequential_edit.empty')
                        );

                        return;
                    }

                    const entity = _.first(response.entities);
                    router.redirectToRoute(
                        'pim_enrich_' + entity.type + '_edit',
                        { id: entity.id }
                    );
                }).always(() => {
                    loadingMask.hide().$el.remove();
                });
            }
        });
    }
);
