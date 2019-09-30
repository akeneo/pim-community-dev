/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getConnectionStatus} from '../fetcher/franklin-connection';
import {AttributesMapping} from '../model/attributes-mapping';
import {FamilyMappingStatus} from '../../react/domain/model/family-mapping-status.enum';

const BaseController = require('pim/controller/front');
const FormBuilder = require('pim/form-builder');

interface Route {
  name: string;
  params: {familyCode: string};
  route: object;
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
  constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public renderForm(route: Route) {
    const promise = new Promise(async resolve => {
      const connectionStatus = await getConnectionStatus(false);
      if (!connectionStatus.isActive) {
        const form = await FormBuilder.build('akeneo-franklin-insights-settings-inactive-connection');

        this.on('pim:controller:can-leave', (event: any) => {
          form.trigger('pim_enrich:form:can-leave', event);
        });
        form.setElement(this.$el).render();

        resolve(form);
      }

      if (!this.active) {
        return resolve();
      }

      this.setNavigationContext(route.params.familyCode);

      const form = await FormBuilder.build('akeneo-franklin-insights-settings-attributes-mapping-edit');
      this.on('pim:controller:can-leave', (event: CanLeaveEvent) => {
        form.trigger('pim_enrich:form:can-leave', event);
      });
      form.on('pim_enrich:form:entity:post_save', async () => {
        await this.updateModel(form, route.params.familyCode);
      });

      await this.updateModel(form, route.params.familyCode);

      resolve(form);
    });

    const deferred = $.Deferred();

    promise.then(deferred.resolve);
    promise.catch(deferred.reject);

    return deferred;
  }

  private setNavigationContext(familyCode: string): void {
    localStorage.setItem(this.config.lastMappingVisitedKey, 'attributes_mapping');
    localStorage.setItem(this.config.lastFamilyVisitedKey, familyCode);
  }

  private async updateModel(form: any, familyCode: string) {
    const model: AttributesMapping = {
      familyCode,
      familyMappingStatus: FamilyMappingStatus.EMPTY,
      hasUnsavedChanges: false,
      attributeCount: 0,
      mappedAttributeCount: 0
    };
    form.setData(model);
    form.trigger('pim_enrich:form:entity:post_fetch');
    form.setElement(this.$el).render();
  }
}

export = EditAttributeMappingController;
