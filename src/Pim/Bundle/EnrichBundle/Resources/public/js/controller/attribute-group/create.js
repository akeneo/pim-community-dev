'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder'
    ],
    function (_, BaseController, FormBuilder) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                if (!this.active) {
                    return;
                }

                return FormBuilder.build('pim-attribute-group-create-form')
                    .then(function (form) {
                        this.on('pim:controller:can-leave', function (event) {
                            form.trigger('pim_enrich:form:can-leave', event);
                        });
                        form.setData({
                            code: '',
                            labels: {}
                        });

                        form.setElement(this.$el).render();
                    }.bind(this));
            }
        });
    }
);
