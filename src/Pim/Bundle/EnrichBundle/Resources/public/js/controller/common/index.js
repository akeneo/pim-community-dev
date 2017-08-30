'use strict';

define(
    [
        'underscore',
        'pim/controller/front',
        'pim/form-builder'
    ],
    function (_, BaseController, FormBuilder) {
        return BaseController.extend({
            initialize: function (options) {
                this.options = options;
            },

            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                return FormBuilder.build('pim-' + this.options.config.entity + '-index')
                    .then((form) => {
                        form.setElement(this.$el).render();

                        return form;
                    });
            }
        });
    }
);
