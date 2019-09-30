/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {isAlreadyMapped} from '../../domain/is-already-mapped';
import {translate} from '../../infrastructure/translator';
import {mapFranklinAttribute} from '../action-creator/family-mapping/map-franklin-attribute';
import {notify} from '../action/notify';
import {NotificationLevel} from '../notification-level';
import {FamilyMappingState} from '../reducer/family-mapping';

export function selectAttribute(familyCode: string, franklinAttributeCode: string, attributeCode: string) {
  return async (dispatch: any, getState: () => FamilyMappingState) => {
    dispatch(mapFranklinAttribute(familyCode, franklinAttributeCode, attributeCode));

    const state = getState();
    if (true === isAlreadyMapped(state.familyMapping.mapping, franklinAttributeCode, attributeCode)) {
      dispatch(
        notify(
          NotificationLevel.ERROR,
          translate('akeneo_franklin_insights.entity.attributes_mapping.constraint.duplicated_pim_attribute')
        )
      );
    }
  };
}
