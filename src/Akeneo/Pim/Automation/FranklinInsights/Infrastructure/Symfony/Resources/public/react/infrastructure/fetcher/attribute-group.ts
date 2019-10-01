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
import {AttributeGroup} from '../../domain/model/attribute-group';

export async function search(
  attributeGroupCodes: string[] = []
): Promise<{[attributeGroupCode: string]: AttributeGroup}> {
  const url = generateUrl('pim_enrich_attributegroup_rest_search', {
    options: {
      limit: -1,
      identifiers: attributeGroupCodes
    }
  });

  const response = await ajax({
    dataType: 'json',
    method: 'POST',
    url
  });

  return response;
}
