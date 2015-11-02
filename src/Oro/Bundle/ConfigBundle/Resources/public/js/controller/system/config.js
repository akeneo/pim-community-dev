'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/product-manager',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error'
    ],
    function (_, BaseController, FormBuilder, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FormBuilder.build('oro-system-config').then(function (form) {
                    form.setElement(this.$el).render();
                }.bind(this));
            }
        });
    }
);
