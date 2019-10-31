/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getJSON} from 'jquery';
import {ALLOWED_ATTRIBUTE_TYPES} from '../../domain/allowed-attribute-types';
import {Attribute} from '../../domain/model/attribute';
import {generateUrl} from '../service/url-generator';

export async function fetchAttributesByFamily(familyCode: string): Promise<{[attributeCode: string]: Attribute}> {
  const url = generateUrl('pim_enrich_attribute_rest_index', {
    families: [familyCode],
    localizable: false,
    is_locale_specific: false,
    scopable: false,
    types: ALLOWED_ATTRIBUTE_TYPES,
    options: {
      limit: 1000
    }
  });

  const apiResponse = await getJSON(url);

  return convertApiResponseToMap(apiResponse);
}

function convertApiResponseToMap(apiResponse: Attribute[]): {[attributeCode: string]: Attribute} {
  const attributes: {[attributeCode: string]: Attribute} = {};

  for (const {code, type, labels, group} of apiResponse) {
    attributes[code] = {code, type, labels, group};
  }

  return attributes;
}
