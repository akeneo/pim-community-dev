'use strict';

/**
 * Attribute group create controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/controller/front',
        'pim/form-builder'
    ],
    function (_, BaseController, FormBuilder) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                if (!this.active) {
                    return;
                }

                return FormBuilder.build('pim-attribute-group-create-form')
                    .then((form) => {
                        this.on('pim:controller:can-leave', function (event) {
                            form.trigger('pim_enrich:form:can-leave', event);
                        });
                        form.setData({
                            code: '',
                            labels: {}
                        });

                        form.setElement(this.$el).render();

                        return form;
                    });
            }
        });
    }
);
