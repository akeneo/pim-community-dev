/* global define */
define([
        'jquery',
        'underscore',
        'routing',
        'oro/datagrid/mass-action',
        'pim/router',
        'pim/provider/sequential-edit-provider'
    ],
    function($, _, Routing, MassAction, router, sequentialEditProvider) {
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
                }).then((entities) => {
                    sequentialEditProvider.set(entities);

                    router.redirectToRoute(
                        'pim_enrich_product_edit',
                        { id: _.first(entities) }
                    );
                });
            }
        });
    }
);
