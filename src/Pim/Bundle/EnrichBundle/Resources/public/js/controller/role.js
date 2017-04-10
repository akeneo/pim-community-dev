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
                securityContext.fetch();
                configProvider.clear();

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
