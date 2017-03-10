'use strict';

define([
        'pim/controller/form',
        'pim/security-context'
    ], function (
        FormController,
        securityContext
    ) {
        return FormController.extend({
            /**
             * Called after a successful submit (after a submitForm)
             *
             * @param {Object} xhr
             */
            afterSubmit: function (xhr) {
                securityContext.fetch();

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
