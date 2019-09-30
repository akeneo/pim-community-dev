/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  addAttributeToFamily as addAttributeToFamilySaver,
  bulkAddAttributesToFamily as bulkAddAttributeToFamilySaver
} from '../../../infrastructure/saver/add-attribute-to-family';
import {translate} from '../../../infrastructure/translator';
import {NotificationLevel} from '../../notification-level';
import {notify} from '../notify';
import {fetchAttributes} from './family-attributes';
import {selectAndFetchFamily} from './family-mapping';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {getFamilyLabel} from '../../get-family-label';

export function addAttributeToFamily(familyCode: string, franklinAttributeCode: string, attributeCode: string) {
  return async (dispatch: any, getState: () => FamilyMappingState) => {
    try {
      const {pimAttributeCode} = await addAttributeToFamilySaver(familyCode, attributeCode);

      dispatch(attributeAddedToFamily(familyCode, franklinAttributeCode, pimAttributeCode));
      dispatch(fetchAttributes(familyCode));

      const familyLabel = getFamilyLabel(getState().family, familyCode);

      dispatch(
        notify(
          NotificationLevel.SUCCESS,
          translate(
            'akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_success',
            {attribute: pimAttributeCode, family: familyLabel},
            1
          )
        )
      );
    } catch (error) {
      dispatch(
        notify(
          NotificationLevel.ERROR,
          translate('akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_error')
        )
      );
    }
  };
}

export function bulkAddAttributesToFamily(familyCode: string, attributeCodes: string[]) {
  return async (dispatch: any, getState: () => FamilyMappingState) => {
    try {
      await bulkAddAttributeToFamilySaver({familyCode, attributeCodes});

      const familyLabel = getFamilyLabel(getState().family, familyCode);

      dispatch(
        notify(
          NotificationLevel.SUCCESS,
          translate(
            'akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_success',
            {family: familyLabel},
            attributeCodes.length
          )
        )
      );
      dispatch(selectAndFetchFamily(familyCode));
    } catch {
      dispatch(
        notify(
          NotificationLevel.ERROR,
          translate('akeneo_franklin_insights.entity.attributes_mapping.flash.add_attribute_to_family_error')
        )
      );
    }
  };
}

export const ATTRIBUTE_ADDED_TO_FAMILY = 'ATTRIBUTE_ADDED_TO_FAMILY';

export interface AttributeAddedToFamilyAction {
  type: typeof ATTRIBUTE_ADDED_TO_FAMILY;
  familyCode: string;
  franklinAttributeCode: string;
  attributeCode: string;
}

export function attributeAddedToFamily(
  familyCode: string,
  franklinAttributeCode: string,
  attributeCode: string
): AttributeAddedToFamilyAction {
  return {
    type: ATTRIBUTE_ADDED_TO_FAMILY,
    familyCode,
    franklinAttributeCode,
    attributeCode
  };
}

export type AddAttributeToFamilyActions = AttributeAddedToFamilyAction;
