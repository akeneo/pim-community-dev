/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getConnectionStatus} from '../fetcher/franklin-connection';
import ConnectionStatus from '../model/connection-status';

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

interface Config {
  lastMappingVisitedKey: string;
}

/**
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class IdentifiersMappingController extends BaseController {
  private readonly config: Config;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super(options);

    this.config = { ...this.config, ...options.config };
  }

  /**
   * {@inheritdoc}
   */
  public renderForm(): any {
    return getConnectionStatus(false)
      .then((connectionStatus: ConnectionStatus) => {
        if (!connectionStatus.isActive) {
          return FormBuilder
            .build('akeneo-franklin-insights-settings-inactive-connection')
            .then((form: any) => {
              this.on('pim:controller:can-leave', (event: any) => {
                form.trigger('pim_enrich:form:can-leave', event);
              });
              form.setElement(this.$el).render();

              return form;
            });
        }

        localStorage.setItem(this.config.lastMappingVisitedKey, 'identifiers_mapping');

        return FormBuilder
          .build('akeneo-franklin-insights-settings-identifiers-mapping-edit')
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

export = IdentifiersMappingController;
