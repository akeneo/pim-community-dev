'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'oro/datagrid/navigate-action',
        'pim/router'
    ],
    function(_, __, NavigateAction, Router) {
        return NavigateAction.extend({
            /**
             * {@inheritdoc}
             */
            getLink: function() {
                return null;
            },
            /**
             * {@inheritdoc}
             */
            execute: function() {
                var route = null;
                var productType = this.model.get('document_type');

                if ('product' === productType) {
                    route = 'pim_enrich_product_edit';
                } else if ('product_model' === productType) {
                    route = 'pim_enrich_product_model_edit';
                } else {
                    Router.displayErrorPage(__('error.common'), 400);

                    return;
                }

                Router.redirectToRoute(route, {id: this.model.get('technical_id')});
            }
        });
    }
);
