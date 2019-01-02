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

      // Reload to page after SSO has been enabled or disabled
      const isEnabledInitialState: boolean = data[0].is_enabled;
      form.on('pim_enrich:form:entity:post_save', () => {
        const isEnabledCurrentState: boolean = form.getFormData().is_enabled;

        if (isEnabledInitialState !== isEnabledCurrentState) {
          setTimeout(() => window.location.reload(), 1000);
        }
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
