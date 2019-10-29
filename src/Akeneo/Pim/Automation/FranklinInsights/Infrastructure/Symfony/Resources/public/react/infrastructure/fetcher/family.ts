/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getJSON} from 'jquery';
import {generateUrl} from '../service/url-generator';

export async function fetchFamilyLabels(familyCode: string): Promise<{[locale: string]: string}> {
  const url = generateUrl('pim_enrich_family_rest_get', {identifier: familyCode});

  const apiResponse = await getJSON(url);

  return apiResponse.labels;
}
