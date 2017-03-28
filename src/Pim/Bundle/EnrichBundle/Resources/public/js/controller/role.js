'use strict';

define([
        'pim/controller/form',
        'pim/security-context',
        'pim/form-config-provider'
    ], function (
        FormController,
        securityContext,
        configProvider
    ) {
        return FormController.extend({
            /**
             * {@inheritdoc}
             */
            afterSubmit: function (xhr) {
                // TODO: drop this when the main menu will be handled as a view
                window.location.reload();

                //securityContext.fetch();
                //configProvider.clear();

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
