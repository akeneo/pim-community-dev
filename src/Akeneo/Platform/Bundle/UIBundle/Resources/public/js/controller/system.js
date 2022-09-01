'use strict';

define(['jquery', 'underscore', 'oro/translator', 'pim/controller/front', 'pim/form-builder', 'routing'], function (
  $,
  _,
  __,
  BaseController,
  FormBuilder,
  Routing
) {
  return BaseController.extend({
    /**
     * {@inheritdoc}
     */
    renderForm: function () {
      return $.when(
        FormBuilder.build('oro-system-config-form'),
        $.get(Routing.generate('oro_config_configuration_system_get'))
      ).then((form, response) => {
        this.on('pim:controller:can-leave', function (event) {
          form.trigger('pim_enrich:form:can-leave', event);
        });
        form.setData(response[0]);
        form.setElement(this.$el).render();

        return form;
      });
    },
  });
});
