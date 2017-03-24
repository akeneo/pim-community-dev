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
             * Called after a successful submit (after a submitForm)
             *
             * @param {Object} xhr
             */
            afterSubmit: function (xhr) {
                securityContext.fetch();
                configProvider.clear();

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
