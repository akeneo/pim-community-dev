/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {ajax} from 'jquery';

const Routing = require('routing');

export interface BulkAddAttributesToFamilyRequest {
  familyCode: string;
  attributeCodes: string[];
}

export async function addAttributeToFamily(
  familyCode: string,
  pimAttributeCode: string
): Promise<{pimAttributeCode: string}> {
  const url = Routing.generate('akeneo_franklin_insights_structure_add_attribute_to_family');

  const request = {
    familyCode,
    attributeCode: pimAttributeCode
  };

  const response = (await ajax({
    url,
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(request)
  })) as {code: string};

  return {pimAttributeCode: response.code};
}

export async function bulkAddAttributesToFamily(request: BulkAddAttributesToFamilyRequest): Promise<void> {
  await ajax({
    url: Routing.generate('akeneo_franklin_insights_structure_bulk_add_attributes_to_family', {
      familyCode: request.familyCode
    }),
    method: 'POST',
    contentType: 'application/json',
    data: JSON.stringify(request.attributeCodes)
  });
}
