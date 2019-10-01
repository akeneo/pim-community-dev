/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {FamilyMappingState} from '../reducer/family-mapping';
import {AttributeMapping} from '../../domain/model/attribute-mapping';

export function selectAttributesThatCanBeAddedToFamily(state: FamilyMappingState): AttributeMapping[] {
  return Object.values(state.familyMapping.mapping).filter((attributeMapping: AttributeMapping) => {
    return attributeMapping.exactMatchAttributeFromOtherFamily !== null;
  });
}
