/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {MapFranklinAttributeAction, MAP_FRANKLIN_ATTRIBUTE} from '../../../domain/action/map-franklin-attribute';

export function mapFranklinAttribute(
  familyCode: string,
  franklinAttributeCode: string,
  attributeCode: string
): MapFranklinAttributeAction {
  return {
    type: MAP_FRANKLIN_ATTRIBUTE,
    familyCode,
    franklinAttributeCode,
    attributeCode
  };
}
