import {ConnectionStatus, getConnectionStatus} from '../fetcher/franklin-connection';

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

interface Config {
  connectionCode: string;
  entity: string;
}

/**
 * Mapping controller. Allows to show an empty page if connection is not activated.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MappingController extends BaseController {
  /**
   * {@inheritdoc}
   */
  public initialize(options: { config: Config }) {
    BaseController.prototype.initialize.apply(this, arguments);
    this.options = options;
  }

  /**
   * {@inheritdoc}
   */
  public renderForm(): object {
    return getConnectionStatus()
      .then((connectionStatus: ConnectionStatus) => {
        const entity = this.options.config.entity;
        let formToBuild = 'pimee-' + entity + '-index-inactive-connection';
        if (connectionStatus.is_active) {
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

export = MappingController;
