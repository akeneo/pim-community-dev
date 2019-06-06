/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {ajax} from 'jquery';
import Family from '../model/family';

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const I18n = require('pim/i18n');
const Messenger = require('oro/messenger');
const Routing = require('routing');
const UserContext = require('pim/user-context');

interface CreateAttributeRequest {
  familyCode: string;
  franklinAttributeLabel: string;
  franklinAttributeType: string;
}

interface CreateAttributeResponse {
  code: string;
}

export default class AttributeSaver {
  static async create(request: CreateAttributeRequest): Promise<CreateAttributeResponse> {
    try {
      const response = await new Promise<CreateAttributeResponse>((resolve, reject) => {
        ajax({
          url: Routing.generate('akeneo_franklin_insights_structure_create_attribute'),
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(request),
        })
          .then(resolve)
          .catch(reject);
      });

      this.notifySuccess(request.familyCode);

      return response;
    } catch(error) {
      this.notifyError();

      throw error;
    }
  }

  private static async notifySuccess(familyCode: string): Promise<void> {
    const family: Family = await FetcherRegistry.getFetcher('family').fetch(familyCode);
    
    const familyLabel = I18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code);
    Messenger.notify(
      'success',
      __('akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_success', {family: familyLabel})
    );
  }

  private static notifyError(): void {
    Messenger.notify(
      'error',
      __('akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_error')
    )
  }
}
