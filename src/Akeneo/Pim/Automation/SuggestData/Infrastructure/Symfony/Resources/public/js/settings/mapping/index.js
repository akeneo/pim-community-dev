'use strict';

define(
    [
        'underscore',
        'pim/controller/common/index',
        'pim/form-builder'
    ],
    function (_, BaseIndex, FormBuilder) {
        return BaseIndex.extend({
            /**
             * {@inheritdoc}
             *
             * This is the same method than the parent, but adding the 'can-leave' mechanism.
             */
            renderForm: function () {
                return FormBuilder.build('pim-' + this.options.config.entity + '-index')
                    .then((form) => {
                        this.on('pim:controller:can-leave', function (event) {
                            form.trigger('pim_enrich:form:can-leave', event);
                        });
                        form.setElement(this.$el).render();

                        return form;
                    });
            }
        });
    }
);
