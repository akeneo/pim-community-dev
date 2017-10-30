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
            initialize() {
                this.useDirectLauncherLink = (null !== this.tab);

                return NavigateAction.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            getLink() {
                const productType = this.model.get('document_type');
                const id = this.model.get('technical_id');

                return Routing.generate('pim_enrich_' + productType + '_edit', { id });
            },

            /**
             * {@inheritdoc}
             */
            run() {
                if (null !== this.tab) {
                    sessionStorage.setItem('redirectTab', `#${this.tab}`);
                }

                return NavigateAction.prototype.run.apply(this, arguments);
            }
        });
    }
);
