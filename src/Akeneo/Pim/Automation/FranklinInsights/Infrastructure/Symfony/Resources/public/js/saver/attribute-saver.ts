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

export interface CreateAttributeRequest {
  familyCode: string;
  franklinAttributeLabel: string;
  franklinAttributeType: string;
}

export interface CreateAttributeResponse {
  code: string;
}

export interface BulkCreateAttributeRequest {
  familyCode: string;
  attributes: Array<{
    franklinAttributeLabel: string;
    franklinAttributeType: string;
  }>;
}

export class AttributeSaver {
  public static async create(request: CreateAttributeRequest): Promise<CreateAttributeResponse> {
    try {
      const response = await ajax({
        url: Routing.generate('akeneo_franklin_insights_structure_create_attribute'),
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(request)
      });

      this.notifySuccess(request.familyCode);

      return response;
    } catch (error) {
      this.notifyError();

      throw error;
    }
  }

  public static async bulkCreate(request: BulkCreateAttributeRequest): Promise<void> {
    try {
      const response = await ajax({
        url: Routing.generate('akeneo_franklin_insights_structure_bulk_create_attribute', {
          familyCode: request.familyCode
        }),
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(request.attributes)
      });

      this.notifySuccess(request.familyCode, request.attributes.length, response.attributesCreatedCount);

      return;
    } catch (error) {
      this.notifyError();

      throw error;
    }
  }

  private static async notifySuccess(familyCode: string, attributeToCreateCount = 1, attributeCreatedCount = 1): Promise<void> {
    const family: Family = await FetcherRegistry.getFetcher('family').fetch(familyCode);

    const familyLabel = I18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code);
    Messenger.notify(
      'success',
      __(
        'akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_success',
        {family: familyLabel, requestCount: attributeToCreateCount, successCount: attributeCreatedCount},
        attributeToCreateCount
      )
    );
  }

  private static notifyError(): void {
    Messenger.notify('error', __('akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_error'));
  }
}

export default AttributeSaver;
