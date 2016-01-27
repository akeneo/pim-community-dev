'use strict';

define(
    ['pim/controller/base', 'pim/fetcher-registry', 'pim/form-builder', 'routing'],
    function (BaseController, FetcherRegistry, FormBuilder, Routing) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.initialize().done(function () {
                    return $.when(
                        FormBuilder.build('oro-system-config-form'),
                        $.get(Routing.generate('oro_config_configuration_system_get'))
                    ).then(function(form, response) {
                        form.setData(response[0]);
                        form.setElement(this.$el).render();
                    }.bind(this));
                }.bind(this));
            }
        });
    }
);
