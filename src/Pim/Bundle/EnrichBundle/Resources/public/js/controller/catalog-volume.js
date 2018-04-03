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
    function ($, _, __, BaseController, FormBuilder, Routing) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function () {
                return $.when(
                    FormBuilder.build('pim-catalog-volume-index'),
                    $.get(Routing.generate('pim_volume_monitoring_get_volumes'))
                ).then((form, data = []) => {

                    this.on('pim:controller:can-leave', function (event) {
                        form.trigger('pim_enrich:form:can-leave', event);
                    });

                    // const dummyData = {
                    //     product_values: { value: 36867028 },
                    //     product_values_average: { value: 326 },
                    //     products: { value: 120000, has_warning: false, type: 'count'},
                    //     attributes_per_family: { value: {average: 75, max: 75 }, has_warning: false, type: 'average_max'},
                    //     channels: { value: 3, has_warning: false, type: 'count'},
                    //     locales: { value: 4, has_warning: false, type: 'count'},
                    //     scopable_attributes:{ value: 2, has_warning: false, type: 'count'},
                    //     localizable_and_scopable_attributes: { value: 4, has_warning: false, type: 'count'},
                    //     localizable_attributes: { value: 8, has_warning: false, type: 'count'},
                    //     families: { value: 24, has_warning: false, type: 'count'},
                    //     attributes: { value: 120, has_warning: false, type: 'count'},
                    //     options_per_attribute: { value: { average: 10, max: 20 }, has_warning: false, type: 'average_max'},
                    //     categories: { value: 10001, has_warning: true, type: 'count'},
                    //     category_trees: { value: 3, has_warning: false, type: 'count'},
                    //     variant_products: { value: 120000, has_warning: false, type: 'count'},
                    //     product_models: { value: 21000, has_warning: false, type: 'count'},

                    //     // EE
                    //     assets: { value: 120, warning: false, type: 'number' },
                    //     asset_categories: { value: 6000, warning: false, type: 'number' },
                    //     asset_category_trees: { value: 2, warning: false, type: 'number'}
                    // };

                    form.setData(data[0]);
                    form.setElement(this.$el).render();

                    return form;
                });
            }
        });
    }
);
