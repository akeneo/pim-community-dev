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
                var productType = this.model.get('document_type');

                Router.redirectToRoute('pim_enrich_' + productType + '_edit', {id: this.model.get('technical_id')});
            }
        });
    }
);
