/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';
import {getConnectionStatus} from '../fetcher/franklin-connection';
import ConnectionStatus from '../model/connection-status';

const __ = require('oro/translator');
const BaseController = require('pim/controller/front');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');
const Router = require('pim/router');

interface Family {
  code: string;
}

interface Config {
  lastFamilyVisitedKey: string;
  lastMappingVisitedKey: string;
}

/**
 * Attribute mapping index controller
 * This controller will load the first mapping, and do a redirect to the edit page.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IndexAttributeMappingController extends BaseController {
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

        return FetcherRegistry.getFetcher('attributes-mapping-by-family')
          .fetchAll()
          .then((families: Family[]) => {
            if (0 === Object.keys(families).length) {

              return $.Deferred().reject({
                status: 404,
                statusText: __('akeneo_franklin_insights.entity.attributes_mapping.module.index.error'),
              });
            }

            const familyCode = families.map((family) => family.code).sort()[0];
            this.setNavigationContext(familyCode);
            Router.redirectToRoute('akeneo_franklin_insights_attributes_mapping_edit', {familyCode});

            return undefined;
          });
      });
  }

  private setNavigationContext(familyCode: string): void {
    localStorage.setItem(this.config.lastMappingVisitedKey, 'attributes_mapping');
    localStorage.setItem(this.config.lastFamilyVisitedKey, familyCode);
  }
}

export = IndexAttributeMappingController;
