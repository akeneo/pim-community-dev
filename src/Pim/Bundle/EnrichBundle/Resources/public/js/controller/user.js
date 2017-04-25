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
            afterSubmit: function () {
                location.reload(); //TODO nav: reload the page to update the menu

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
