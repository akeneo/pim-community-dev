/* global define */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'oro/datagrid/mass-action',
        'pim/router',
        'oro/messenger',
        'pim/provider/sequential-edit-provider'
    ],
    function($, _, __, Routing, MassAction, router, messenger, sequentialEditProvider) {
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

                return $.ajax({
                    url: Routing.generate('pim_enrich_sequential_edit_rest_get_ids'),
                    data: params
                }).then((response) => {
                    sequentialEditProvider.set(response.entities);

                    if (response.total > 1000) {
                        messenger.notify(
                            'warning',
                            __('pim_enrich.entity.product.sequential_edit.item_limit', {'count': response.total})
                        );
                    }
                    router.redirectToRoute(
                        'pim_enrich_product_edit',
                        { id: _.first(response.entities) }
                    );
                });
            }
        });
    }
);
