'use strict';

define([
        'pim/controller/form'
    ], function (
        FormController
    ) {
        return FormController.extend({
            /**
             * {@inheritdoc}
             */
            afterSubmit: function (xhr) {
                // If some validation errors are raised by the backend,
                // do not hard reload the page in order to keep error message displayed to the user.
                if (xhr.responseText.indexOf('validation-tooltip') < 0) {
                    window.location.reload(); //TODO nav: reload the page to update the menu
                }

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
