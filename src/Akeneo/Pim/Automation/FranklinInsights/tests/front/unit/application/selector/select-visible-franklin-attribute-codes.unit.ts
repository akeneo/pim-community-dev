/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {FamilyMappingState} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/reducer/family-mapping';
import {selectFilteredFranklinAttributeCodes} from '../../../../../Infrastructure/Symfony/Resources/public/react/application/selector/select-visible-franklin-attribute-codes';
import {AttributeMappingStatus} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {FranklinAttributeType} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';

it('selects franklin attribute codes without filters', async () => {
  const resultsNoFilters = selectFilteredFranklinAttributeCodes(getState(null));

  expect(resultsNoFilters).toContain('connector_type(s)');
  expect(resultsNoFilters).toContain('iso_sensitivity');
  expect(resultsNoFilters).toContain('weight');
});

it('selects filtered franklin attribute codes in terms of code', async () => {
  const resultFromCodeTy = selectFilteredFranklinAttributeCodes(getState(null, 'ty'));

  expect(resultFromCodeTy).toContain('connector_type(s)');
  expect(resultFromCodeTy).toContain('iso_sensitivity');
  expect(resultFromCodeTy).not.toContain('weight');

  const resultFromCodeIso = selectFilteredFranklinAttributeCodes(getState(null, 'iso_sens'));

  expect(resultFromCodeIso).toContain('iso_sensitivity');
  expect(resultFromCodeIso).not.toContain('connector_type(s)');
  expect(resultFromCodeIso).not.toContain('weight');
});

it('selects filtered franklin attribute codes in terms of label', async () => {
  const resultFromLabelConnector = selectFilteredFranklinAttributeCodes(getState(null, 'Iso Sensitivity'));

  expect(resultFromLabelConnector).toContain('iso_sensitivity');
  expect(resultFromLabelConnector).not.toContain('connector_type(s)');
  expect(resultFromLabelConnector).not.toContain('weight');
});

it('selects filtered franklin attribute codes in terms of label with special characters', async () => {
  const resultFromLabelConnector = selectFilteredFranklinAttributeCodes(getState(null, '^Connector type(s)$'));

  expect(resultFromLabelConnector).toContain('connector_type(s)');
  expect(resultFromLabelConnector).not.toContain('iso_sensitivity');
  expect(resultFromLabelConnector).not.toContain('weight');
});

it('selects filtered franklin attribute codes in terms of label and it is case insensitive', async () => {
  const resultFromLabelConnector = selectFilteredFranklinAttributeCodes(getState(null, 'iso sensitivity'));

  expect(resultFromLabelConnector).toContain('iso_sensitivity');
  expect(resultFromLabelConnector).not.toContain('connector_type(s)');
  expect(resultFromLabelConnector).not.toContain('weight');
});

it('selects filtered franklin attribute codes in terms of status', async () => {
  const resultFromInactive = selectFilteredFranklinAttributeCodes(getState('inactive'));

  expect(resultFromInactive).toContain('iso_sensitivity');
  expect(resultFromInactive).not.toContain('connector_type(s)');
  expect(resultFromInactive).not.toContain('weight');
});

it('selects filtered franklin attribute codes in terms of status and label', async () => {
  const resultPending = selectFilteredFranklinAttributeCodes(getState('pending', '^Connector type(s)$'));

  expect(resultPending).toContain('connector_type(s)');
  expect(resultPending).not.toContain('iso_sensitivity');
  expect(resultPending).not.toContain('weight');

  const results = selectFilteredFranklinAttributeCodes(getState('inactive', '^Connector type(s)$'));

  expect(results).not.toContain('connector_type(s)');
  expect(results).not.toContain('iso_sensitivity');
  expect(results).not.toContain('weight');
});

function getState(status: AttributeMappingStatus | null, codeOrLabel?: string): FamilyMappingState {
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
          canCreateAttribute: true,
          status: AttributeMappingStatus.ACTIVE,
          exactMatchAttributeFromOtherFamily: null
        }
      }
    },
    searchFranklinAttributes: {
      codeOrLabel,
      status
    },
    selectedFranklinAttributeCodes: []
  };
}
