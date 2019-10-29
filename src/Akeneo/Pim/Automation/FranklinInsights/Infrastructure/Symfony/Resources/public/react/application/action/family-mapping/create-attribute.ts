/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {
  bulkCreateAttributes as bulkCreate,
  createAttribute as create
} from '../../../infrastructure/saver/create-attribute';
import {fetchAttributes} from './family-attributes';
import {translate} from '../../../infrastructure/translator';
import {NotificationLevel} from '../../notification-level';
import {notify} from '../notify';
import {selectAndFetchFamily} from './family-mapping';
import {FamilyMappingState} from '../../reducer/family-mapping';
import {getFamilyLabel} from '../../get-family-label';

export function bulkCreateAttributes(
  familyCode: string,
  attributes: Array<{franklinAttributeLabel: string; franklinAttributeType: string}>
) {
  return async (dispatch: any, getState: () => FamilyMappingState) => {
    try {
      const {attributesCreatedNumber} = await bulkCreate({familyCode, attributes});

      const familyLabel = getFamilyLabel(getState().family, familyCode);

      dispatch(
        notify(
          NotificationLevel.SUCCESS,
          translate(
            'akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_success',
            {family: familyLabel, requestCount: attributes.length, successCount: attributesCreatedNumber},
            attributesCreatedNumber
          )
        )
      );

      dispatch(selectAndFetchFamily(familyCode));
    } catch {
      dispatch(
        notify(
          NotificationLevel.ERROR,
          translate('akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_error')
        )
      );
    }
  };
}

export function createAttribute(
  familyCode: string,
  franklinAttributeCode: string,
  franklinAttributeType: string,
  franklinAttributeLabel: string
) {
  return async (dispatch: any, getState: () => FamilyMappingState) => {
    try {
      const {attributeCode} = await create(familyCode, franklinAttributeLabel, franklinAttributeType);

      dispatch(attributeCreated(familyCode, franklinAttributeCode, attributeCode));
      dispatch(fetchAttributes(familyCode));

      const familyLabel = getFamilyLabel(getState().family, familyCode);

      dispatch(
        notify(
          NotificationLevel.SUCCESS,
          translate(
            'akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_success',
            {family: familyLabel},
            1
          )
        )
      );
    } catch (error) {
      dispatch(
        notify(
          NotificationLevel.ERROR,
          translate('akeneo_franklin_insights.entity.attributes_mapping.flash.create_attribute_error')
        )
      );
    }
  };
}

export const ATTRIBUTE_CREATED = 'ATTRIBUTE_CREATED';

export interface AttributeCreatedAction {
  type: typeof ATTRIBUTE_CREATED;
  familyCode: string;
  franklinAttributeCode: string;
  attributeCode: string;
}

export function attributeCreated(
  familyCode: string,
  franklinAttributeCode: string,
  attributeCode: string
): AttributeCreatedAction {
  return {
    type: ATTRIBUTE_CREATED,
    familyCode,
    franklinAttributeCode,
    attributeCode
  };
}

export type CreateAttributeActions = AttributeCreatedAction;
