/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {ajax} from 'jquery';

import {generateUrl} from '../service/url-generator';

const Routing = require('routing');

export interface BulkCreateAttributeRequest {
  familyCode: string;
  attributes: Array<{
    franklinAttributeLabel: string;
    franklinAttributeType: string;
  }>;
}

export async function createAttribute(
  familyCode: string,
  franklinAttributeLabel: string,
  franklinAttributeType: string
): Promise<{attributeCode: string}> {
  const url = generateUrl('akeneo_franklin_insights_structure_create_attribute');

  const request = {
    familyCode,
    franklinAttributeLabel,
    franklinAttributeType
  };

  const response = (await ajax({
    url,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(request)
  })) as {code: string};

  return {attributeCode: response.code};
}

export async function bulkCreateAttributes(
  request: BulkCreateAttributeRequest
): Promise<{attributesCreatedNumber: number}> {
  const response = await ajax({
    url: Routing.generate('akeneo_franklin_insights_structure_bulk_create_attribute', {
      familyCode: request.familyCode
    }),
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(request.attributes)
  });

  return {attributesCreatedNumber: response.attributesCreatedCount};
}
