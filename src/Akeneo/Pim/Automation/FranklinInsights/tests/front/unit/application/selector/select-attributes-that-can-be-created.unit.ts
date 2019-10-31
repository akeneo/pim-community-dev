/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributeMappingStatus} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {FamilyMappingState} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping';
import {FranklinAttributeType} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';
import {selectAttributesThatCanBeCreated} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/selector/select-attributes-that-can-be-created';

test('it selects attributes that can be created from state', () => {
  const attributes = selectAttributesThatCanBeCreated(getState());

  expect(attributes).toContainEqual({
    franklinAttribute: {
      code: 'connector_type(s)',
      label: '^Connector type(s)$',
      type: FranklinAttributeType.TEXT,
      summary: []
    },
    attribute: null,
    canCreateAttribute: true,
    status: AttributeMappingStatus.PENDING,
    exactMatchAttributeFromOtherFamily: null
  });
  expect(attributes).toContainEqual({
    franklinAttribute: {
      code: 'iso_sensitivity',
      label: 'Iso Sensitivity',
      type: FranklinAttributeType.TEXT,
      summary: []
    },
    attribute: null,
    canCreateAttribute: true,
    status: AttributeMappingStatus.INACTIVE,
    exactMatchAttributeFromOtherFamily: null
  });
  expect(attributes).not.toContainEqual({
    franklinAttribute: {
      code: 'weight',
      label: 'Weight',
      type: FranklinAttributeType.TEXT,
      summary: []
    },
    attribute: 'pim_weight',
    canCreateAttribute: false,
    status: AttributeMappingStatus.ACTIVE,
    exactMatchAttributeFromOtherFamily: null
  });
});

function getState(): FamilyMappingState {
  return {
    familyMapping: {
      familyCode: 'camera',
      mapping: {
        ['connector_type(s)']: {
          franklinAttribute: {
            code: 'connector_type(s)',
            label: '^Connector type(s)$',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: null,
          canCreateAttribute: true,
          status: AttributeMappingStatus.PENDING,
          exactMatchAttributeFromOtherFamily: null
        },
        ['iso_sensitivity']: {
          franklinAttribute: {
            code: 'iso_sensitivity',
            label: 'Iso Sensitivity',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: null,
          canCreateAttribute: true,
          status: AttributeMappingStatus.INACTIVE,
          exactMatchAttributeFromOtherFamily: null
        },
        ['weight']: {
          franklinAttribute: {
            code: 'weight',
            label: 'Weight',
            type: FranklinAttributeType.TEXT,
            summary: []
          },
          attribute: 'pim_weight',
          canCreateAttribute: false,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      }
    },
    searchFranklinAttributes: {
      codeOrLabel: undefined,
      status: null
    },
    selectedFranklinAttributeCodes: []
  };
}
