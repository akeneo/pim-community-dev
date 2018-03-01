'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/controller/front',
        'pim/form-builder',
        'routing'
    ],
    function ($, _, __, BaseController, FormBuilder) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                return $.when(FormBuilder.build('pim-catalog-volume-index')).then((form) => {
                    this.on('pim:controller:can-leave', function (event) {
                        form.trigger('pim_enrich:form:can-leave', event);
                    });

                    const dummyData = {
                        product_values: { value: 36867028 },
                        product_values_average: { value: 326 },
                        products: { value: 120000, warning: false, type: 'number'},
                        attributes_by_family: { value: {mean: 75, max: 75 }, warning: false, type: 'mean_max'},
                        channels: { value: 3, warning: false, type: 'number'},
                        locales: { value: 4, warning: false, type: 'number'},
                        scopable_attributes:{ value: 2, warning: false, type: 'number'},
                        localizable_scopable_attributes: { value: 4, warning: false, type: 'number'},
                        localizable_attributes: { value: 8, warning: false, type: 'number'},
                        families: { value: 24, warning: false, type: 'number'},
                        attributes: { value: 120, warning: false, type: 'number'},
                        options_by_attribute: { value: { mean: 10, max: 20 }, warning: false, type: 'mean_max'},
                        categories: { value: 10001, warning: true, type: 'number'},
                        category_trees: { value: 3, warning: false, type: 'number'},
                        variant_products: { value: 120000, warning: false, type: 'number'},
                        product_models: { value: 21000, warning: false, type: 'number'},

                        // EE
                        assets: { value: 120, warning: false, type: 'number' },
                        asset_categories: { value: 6000, warning: false, type: 'number' },
                        asset_category_trees: { value: 2, warning: false, type: 'number'}
                    };

                    form.setData(dummyData);
                    form.setElement(this.$el).render();

                    return form;
                });
            }
        });
    }
);
