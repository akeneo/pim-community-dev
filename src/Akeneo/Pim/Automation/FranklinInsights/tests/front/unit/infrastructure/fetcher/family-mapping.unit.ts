import {fetchByFamilyCode} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/family-mapping';
import {getJSON} from 'jquery';
import {FranklinAttributeType} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';
import {AttributeMappingStatus} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-mapping-status.enum';
import {generateUrl} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator';

jest.mock('jquery');
jest.mock('../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator');

beforeEach(() => {
  jest.resetModules();
});

describe('Infrastructure > Fetcher > Family mapping', () => {
  test('It gets family mapping by family code', async () => {
    const familyCode = 'MyFamilyCode';
    const url = `/franklin-insights/mapping/attributes/${familyCode}`;
    const mapping = {
      my_franklin_attribute_code: {
        franklinAttribute: {
          code: 'my_franklin_attribute_code',
          label: 'my franklin attribute label',
          type: FranklinAttributeType.TEXT,
          summary: ''
        },
        attribute: null,
        status: AttributeMappingStatus.PENDING,
        exactMatchAttributeFromOtherFamily: null,
        canCreateAttribute: true
      }
    };
    const apiResponse = {
      mapping
    };

    generateUrl.mockReturnValue(url);
    getJSON.mockResolvedValue(apiResponse);

    const response = await fetchByFamilyCode(familyCode);

    expect(generateUrl).toHaveBeenCalledWith('akeneo_franklin_insights_attributes_mapping_get', {
      identifier: familyCode
    });
    expect(response).toMatchObject(mapping);
  });
});
