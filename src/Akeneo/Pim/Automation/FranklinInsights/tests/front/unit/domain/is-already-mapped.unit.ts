/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {isAlreadyMapped} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/is-already-mapped';
import {AttributesMapping} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attributes-mapping';
import {AttributeMappingStatus} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {FranklinAttributeType} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';

describe('Domain > is already mapped', () => {
  test('it determines that an attribute is already mapped', () => {
    const isMapped = isAlreadyMapped(getMapping(), 'iso_sensitivity', 'pim_weight');
    expect(isMapped).toBe(true);
  });

  test('it determines that an attribute is not already mapped', () => {
    const isMapped = isAlreadyMapped(getMapping(), 'iso_sensitivity', 'pim_sensitivity');
    expect(isMapped).toBe(false);
  });

  test('it determines that an attribute is not already mapped if it is undefined', () => {
    const isMapped = isAlreadyMapped(getMapping(), 'iso_sensitivity', undefined);
    expect(isMapped).toBe(false);
  });

  test('it determines that an attribute is not already mapped if it is null', () => {
    const isMapped = isAlreadyMapped(getMapping(), 'iso_sensitivity', null);
    expect(isMapped).toBe(false);
  });
});

function getMapping(): AttributesMapping {
  return {
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
  };
}
