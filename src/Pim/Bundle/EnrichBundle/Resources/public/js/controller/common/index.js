'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
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
            renderRoute: function () {
                return FormBuilder.build('pim-' + this.options.config.entity + '-index')
                    .then(function (form) {
                        form.setElement(this.$el).render();
                    }.bind(this));
            }
        });
    }
);
