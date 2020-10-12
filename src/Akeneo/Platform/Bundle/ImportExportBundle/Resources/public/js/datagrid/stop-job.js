'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'oro/datagrid/abstract-action',
        'pim/router'
    ],
    function(_, __, AbstractAction, Router) {
        return AbstractAction.extend({
            /**
             * {@inheritdoc}
             */
            execute: function() {
              debugger;
              console.log(this.model);
                // var route = null;
                // var productType = this.model.get('product_type');

                // if ('product' === productType) {
                //     route = 'pim_enrich_product_edit';
                // } else if ('product_model' === productType) {
                //     route = 'pim_enrich_product_model_edit';
                // } else {
                //     Router.displayErrorPage(__('error.common'), 400);

                //     return;
                // }

                // Router.redirectToRoute(route, {id: this.model.get('technical_id')});
            }
        });
    }
);
