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

                    form.setData(data[0]);
                    form.setElement(this.$el).render();

                    return form;
                });
            }
        });
    }
);
