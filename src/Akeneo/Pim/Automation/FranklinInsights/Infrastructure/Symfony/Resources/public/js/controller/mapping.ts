/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getConnectionStatus} from '../fetcher/franklin-connection';
import ConnectionStatus from '../model/connection-status';

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');

interface Config {
  lastFamilyVisitedKey: string;
  lastMappingVisitedKey: string;
}

/**
 * This controller will redirect to the last visited page in mapping or show an empty page if the connection status
 * is not activated.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class MappingController extends BaseController {
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
  public renderForm(): JQueryPromise<any> {
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

        const lastMappingVisited: string | null = localStorage.getItem(this.config.lastMappingVisitedKey);
        const lastFamilyVisited: string | null = localStorage.getItem(this.config.lastFamilyVisitedKey);
        if ('attributes_mapping' === lastMappingVisited && null !== lastFamilyVisited) {
          Router.redirectToRoute('akeneo_franklin_insights_attributes_mapping_edit', {familyCode: lastFamilyVisited});

          return;
        }

        if ('attributes_mapping' === lastMappingVisited) {
          Router.redirectToRoute('akeneo_franklin_insights_attributes_mapping_index');

          return;
        }

        Router.redirectToRoute('akeneo_franklin_insights_identifiers_mapping_edit');

        return;
      });
  }
}

export = MappingController;
