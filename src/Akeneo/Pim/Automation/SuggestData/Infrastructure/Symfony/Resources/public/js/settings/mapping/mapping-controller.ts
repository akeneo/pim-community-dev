import {isConnectionActivated} from 'akeneosuggestdata/js/pim-ai/fetcher/connection-fetcher';
const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

/**
 * Mapping controller. Allows to show an empty page if connection is not activated.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MappingController extends BaseController {
  /**
   * {@inheritdoc}
   */
  initialize(options: { config: { connectionCode: string, entity: string } }) {
    BaseController.prototype.initialize.apply(this, arguments);
    this.options = options;
  }

  /**
   * {@inheritdoc}
   */
  renderForm() {
    return isConnectionActivated(this.options.config.connectionCode)
      .then(connectionIsActivated => {
        const entity = this.options.config.entity;
        let formToBuild = 'pimee-' + entity + '-index-inactive-connection';
        if (connectionIsActivated) {
          formToBuild = 'pimee-' + entity + '-index';
        }
        return FormBuilder
          .build(formToBuild)
          .then((form: any) => {
            this.on('pim:controller:can-leave', (event: any) => {
              form.trigger('pim_enrich:form:can-leave', event);
            });
            form.setElement(this.$el).render();
            return form;
          });
      });
  }
}

export = MappingController
