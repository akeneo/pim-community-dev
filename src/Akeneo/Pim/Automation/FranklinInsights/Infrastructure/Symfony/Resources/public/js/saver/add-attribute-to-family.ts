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

// @todo[DAPI-280] define the request interface
interface AddAttributeToFamilyRequest {
  familyCode: string;
  attributeCode: string;
}
interface AddAttributeToFamilyResponse {
  code: string;
}

export default class AddAttributeToFamily {

  public static async add(request: AddAttributeToFamilyRequest): Promise<AddAttributeToFamilyResponse> {
    try {
      const response = await new Promise<AddAttributeToFamilyResponse>((resolve, reject) => {
        ajax({
          url: Routing.generate('akeneo_franklin_insights_structure_attach_attribute_to_family'),
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify(request)
        })
          .then(resolve)
          .catch(reject);
      });

      this.notifySuccess(request.familyCode, request.attributeCode);

      return response;
    } catch (error) {
      this.notifyError();

      throw error;
    }
  }

  private static async notifySuccess(familyCode: string, attributeCode: string): Promise<void> {
    const family: Family = await FetcherRegistry.getFetcher('family').fetch(familyCode);

    const familyLabel = I18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code);
    Messenger.notify(
      'success',
      __('akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_success', {family: familyLabel, attribute: attributeCode})
    );
  }

  private static notifyError(): void {
    Messenger.notify('error', __('akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_error'));
  }
}
