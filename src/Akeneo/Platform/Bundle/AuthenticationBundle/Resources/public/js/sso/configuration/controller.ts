import BaseView = require('pimui/js/view/base');
import * as $ from 'jquery';

const Routing = require('routing');
const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

class ConfigurationController extends BaseController {
  renderForm() {
    return $.when(
      FormBuilder.build('authentication-sso-configuration'),
      $.get(Routing.generate('authentication_sso_configuration_get'))
    ).then((form: BaseView, data = []) => {
      this.on('pim:controller:can-leave', (event: {canLeave: boolean}) => {
        form.trigger('pim_enrich:form:can-leave', event);
      });

      form.setData(data[0]);
      form
        .setElement(this.$el)
        .render()
      ;

      return form;
    });
  }
}

export = ConfigurationController;
