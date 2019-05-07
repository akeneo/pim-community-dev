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
import BaseView = require('pimui/js/view/base');

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');
const FetcherRegistry = require('pim/fetcher-registry');

interface Route {
  name: string;
  params: { familyCode: string };
  route: object;
}

interface FamilyMapping {
  code: string;
  enabled: boolean;
  mapping: Array<{[index: string]: string}>;
}

interface CanLeaveEvent {
  canLeave: boolean;
}

interface Config {
  lastFamilyVisitedKey: string;
  lastMappingVisitedKey: string;
}

/**
 * Attribute mapping edit controller
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class EditAttributeMappingController extends BaseController {
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
  public renderForm(route: Route): any {
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

        return FetcherRegistry
          .getFetcher('attributes-mapping-by-family')
          .fetch(route.params.familyCode, {cached: false})
          .then((familyMapping: FamilyMapping) => {
            if (!this.active) {
              return;
            }

            this.setNavigationContext(route.params.familyCode);

            return FormBuilder.build('akeneo-franklin-insights-settings-attributes-mapping-edit')
              .then((form: BaseView) => {
                this.on('pim:controller:can-leave', (event: CanLeaveEvent) => {
                  form.trigger('pim_enrich:form:can-leave', event);
                });
                form.setData(familyMapping);
                form.trigger('pim_enrich:form:entity:post_fetch', familyMapping);
                form.setElement(this.$el).render();

                form.on('pim_enrich:form:entity:post_save', () => {
                  FetcherRegistry
                    .getFetcher('attributes-mapping-by-family')
                    .fetch(route.params.familyCode, {cached: false})
                    .then((savedFamilyMapping: FamilyMapping) => {
                      form.setData(savedFamilyMapping);
                      form.trigger('pim_enrich:form:entity:post_fetch', savedFamilyMapping);
                      form.setElement(this.$el).render();
                    });
                });

                return form;
              });
          });
      });
  }

  private setNavigationContext(familyCode: string): void {
    localStorage.setItem(this.config.lastMappingVisitedKey, 'attributes_mapping');
    localStorage.setItem(this.config.lastFamilyVisitedKey, familyCode);
  }
}

export = EditAttributeMappingController;
