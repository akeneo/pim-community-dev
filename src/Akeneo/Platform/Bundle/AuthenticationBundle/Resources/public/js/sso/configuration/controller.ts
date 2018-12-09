import BaseView = require('pimui/js/view/base');

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

class ConfigurationController extends BaseController {
  renderForm() {
    return FormBuilder.build('authentication-sso-configuration')
      .then((form: BaseView) => {
        this.on('pim:controller:can-leave', (event: {canLeave: boolean}) => {
          form.trigger('pim_enrich:form:can-leave', event);
        });

        form.setElement(this.$el).render();

        return form;
      });
  }
}

export = ConfigurationController;
