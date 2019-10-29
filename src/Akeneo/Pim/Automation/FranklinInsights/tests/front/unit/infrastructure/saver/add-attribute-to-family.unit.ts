import {ajax} from 'jquery';
import {
  addAttributeToFamily,
  bulkAddAttributesToFamily
} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/add-attribute-to-family';

const Routing = require('routing');

jest.mock('jquery');
jest.mock('routing');

beforeEach(() => {
  jest.resetModules();
});

describe('Infrastructure > Save > Add attribute to family', () => {
  test('it adds an attribute to the family', async () => {
    const familyCode = 'family_code';
    const pimAttributeCode = 'connector_type';
    const route = 'akeneo_franklin_insights_structure_add_attribute_to_family';
    const ajaxResponse = {code: 'ATT_255'};

    Routing.generate.mockReturnValue('http://pim.example.com/addAttributeToFamily');
    ajax.mockResolvedValue(ajaxResponse);

    const response = await addAttributeToFamily(familyCode, pimAttributeCode);

    expect(Routing.generate).toHaveBeenCalledWith(route);
    expect(ajax).toHaveBeenCalledWith({
      url: 'http://pim.example.com/addAttributeToFamily',
      method: 'POST',
      contentType: 'application/json',
      data: '{"familyCode":"family_code","attributeCode":"connector_type"}'
    });
    expect(response.pimAttributeCode).toBe('ATT_255');
  });

  test('It adds multiple attributes to a family', async () => {
    const familyCode = 'MyFamilyCode';
    const attributeCodes = ['connector_type'];
    const url = `${familyCode}`;
    const ajaxResponse = {};

    Routing.generate.mockReturnValue(url);
    ajax.mockResolvedValue(ajaxResponse);

    const response = await bulkAddAttributesToFamily({
      familyCode,
      attributeCodes
    });

    expect(Routing.generate).toHaveBeenCalledWith('akeneo_franklin_insights_structure_bulk_add_attributes_to_family', {
      familyCode
    });
    expect(ajax).toHaveBeenCalledWith({
      url,
      method: 'POST',
      contentType: 'application/json',
      data: '["connector_type"]'
    });
    expect(response).toBeUndefined();
  });
});
