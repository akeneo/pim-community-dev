'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/page-title',
        'pim/error',
        'routing'
    ],
    function ($, _, BaseController, FormBuilder, PageTitle, Error, Routing) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function () {
                return $.when(
                    FormBuilder.build('oro-system-config-form'),
                    $.get(Routing.generate('oro_config_configuration_system_get'))
                ).then(function(form, response) {
                    this.on('pim:controller:can-leave', function (event) {
                        form.trigger('pim_enrich:form:can-leave', event);
                    });
                    form.setData(response[0]);
                    form.setElement(this.$el).render();
                }.bind(this)).fail(function (response) {
                    var errorView = new Error(response.responseJSON.message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
