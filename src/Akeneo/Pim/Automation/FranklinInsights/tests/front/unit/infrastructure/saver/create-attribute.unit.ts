import {ajax} from 'jquery';
import {
  bulkCreateAttributes,
  createAttribute
} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/create-attribute';
import {generateUrl} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator';

const Routing = require('routing');

jest.mock('jquery');
jest.mock('routing');
jest.mock('../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator');

beforeEach(() => {
  jest.resetModules();
});

describe('Infrastructure > Save > Attribute', () => {
  test('it creates an attribute', async () => {
    const familyCode = 'family_code';
    const franklinAttributeLabel = 'franklin_attr_label';
    const franklinAttributeType = 'franklin_attr_type';
    const route = 'akeneo_franklin_insights_structure_create_attribute';
    const ajaxResponse = {code: 'ATT_255'};

    generateUrl.mockReturnValue('http://pim.example.com/attribute');
    ajax.mockResolvedValue(ajaxResponse);

    const response = await createAttribute(familyCode, franklinAttributeLabel, franklinAttributeType);

    expect(generateUrl).toHaveBeenCalledWith(route);
    expect(ajax).toHaveBeenCalledWith({
      url: 'http://pim.example.com/attribute',
      method: 'POST',
      contentType: 'application/json',
      data:
        '{"familyCode":"family_code","franklinAttributeLabel":"franklin_attr_label","franklinAttributeType":"franklin_attr_type"}'
    });
    expect(response.attributeCode).toBe('ATT_255');
  });

  test('it creates multiple attributes', async () => {
    const familyCode = 'family_code';
    const attributes = [
      {
        franklinAttributeLabel: 'franklin_attr_label',
        franklinAttributeType: 'franklin_attr_type'
      }
    ];
    const route = 'akeneo_franklin_insights_structure_bulk_create_attribute';
    const url = `/franklin-insights/structure/${familyCode}/create-attribute/bulk`;
    const ajaxResponse = {attributesCreatedCount: 1};

    Routing.generate.mockReturnValue(url);

    ajax.mockResolvedValue(ajaxResponse);

    const response = await bulkCreateAttributes({
      familyCode,
      attributes
    });

    expect(Routing.generate).toHaveBeenCalledWith(route, {
      familyCode
    });
    expect(ajax).toHaveBeenCalledWith({
      url,
      method: 'POST',
      contentType: 'application/json',
      data: '[{"franklinAttributeLabel":"franklin_attr_label","franklinAttributeType":"franklin_attr_type"}]'
    });
    expect(response.attributesCreatedNumber).toBe(1);
  });
});
