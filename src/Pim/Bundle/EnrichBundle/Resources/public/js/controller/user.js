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
                if(xhr.responseText.indexOf('validation-tooltip') < 0)
                {
                    window.location.reload(); //TODO nav: reload the page to update the menu
                }

                FormController.prototype.afterSubmit.apply(this, arguments);
            }
        });
    }
);
