/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {Dispatch} from 'redux';

import {translate} from '../../../infrastructure/translator';
import {NotificationLevel} from '../../notification-level';
import {notify} from '../notify';

export function deactivateFranklinAttributeMapping(familyCode: string, franklinAttributeCode: string) {
  return async (dispatch: Dispatch) => {
    dispatch(franklinAttributeMappingDeactivated(familyCode, franklinAttributeCode));

    dispatch(
      notify(
        NotificationLevel.SUCCESS,
        translate('akeneo_franklin_insights.entity.attributes_mapping.flash.do_not_map_attribute_success')
      )
    );
  };
}

export const FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED = 'FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED';

export interface FranklinAttributeMappingDeactivatedAction {
  type: typeof FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED;
  familyCode: string;
  franklinAttributeCode: string;
}

export function franklinAttributeMappingDeactivated(
  familyCode: string,
  franklinAttributeCode: string
): FranklinAttributeMappingDeactivatedAction {
  return {
    type: FRANKLIN_ATTRIBUTE_MAPPING_DEACTIVATED,
    familyCode,
    franklinAttributeCode
  };
}

export type DeactivateFranklinAttributeMappingActions = FranklinAttributeMappingDeactivatedAction;
