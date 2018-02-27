'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'pim/page-title',
        'routing'
    ],
    function ($, _, __, BaseController, FormBuilder, PageTitle, Routing) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                return $.when(
                    FormBuilder.build('pim-catalog-volume-index'),
                ).then((form, response) => {
                    this.on('pim:controller:can-leave', function (event) {
                        form.trigger('pim_enrich:form:can-leave', event);
                    });

                    const dummyData = {
                        product_values: { value: 36867028, warning: false },
                        products: { value: 120000, warning: false },
                        attributes_by_family: { value: {mean: 75, max: 75 }, warning: false },
                        channels: { value: 3, warning: false },
                        locales: { value: 4, warning: false },
                        scopable_attributes:{ value: 2, warning: false },
                        localizable_scopable_attributes: { value: 4, warning: false },
                        localizable_attributes: { value: 8, warning: false },
                        families: { value: 24, warning: false },
                        attributes: { value: 120, warning: false },
                        options_by_attribute: { value: { mean: 10, max: 20 }, warning: false },
                        categories: { value: 10001, warning: true },
                        variant_products: { value: 120000, warning: false },
                        product_models: { value: 21000, warning: false }
                    };

                    form.setData(dummyData);
                    form.setElement(this.$el).render();

                    return form;
                });
            }
        });
    }
);
