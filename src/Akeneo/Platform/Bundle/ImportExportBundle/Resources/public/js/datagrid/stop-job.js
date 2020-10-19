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
            initialize: function(options) {
                AbstractAction.prototype.initialize.apply(this, arguments);

                const isStoppable = options.model.get('isStoppable') === '1';
                this.launcherOptions = {
                    ...this.launcherOptions,
                    className: `${this.launcherOptions.className} ${isStoppable ? '' : ' AknButton--hidden'}`
                };

            },

            /**
             * {@inheritdoc}
             */
            execute: function() {
                const isStoppable = this.model.get('isStoppable') === '1';
                if (isStoppable) {
                    AbstractAction.prototype.execute.apply(this, arguments);
                }
            }
        });
    }
);
