import {ajax} from 'jquery';
import {saveFamilyMapping} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/saver/family-mapping';
import {AttributesMapping} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attributes-mapping';

const Routing = require('routing');

jest.mock('jquery');
jest.mock('routing');

beforeEach(() => {
  jest.resetModules();
});

describe('Infrastructure > Saver > Family Mapping', () => {
  test('It saves the attribute mapping for the family', async () => {
    const familyCode = '';
    const mapping = {
      color: {
        franklinAttribute: {
          code: 'color',
          label: 'Color',
          type: 'text',
          summary: ['white']
        },
        attribute: 'color',
        status: 'pending',
        exactMatchAttributeFromOtherFamily: 'color',
        canCreateAttribute: false
      }
    } as AttributesMapping;
    const route = 'akeneo_franklin_insights_attributes_mapping_update';
    const url = `/franklin-insights/mapping/attributes/${familyCode}`;
    const ajaxResponse = {};

    Routing.generate.mockReturnValue(url);
    ajax.mockResolvedValue(ajaxResponse);

    const response = await saveFamilyMapping(familyCode, mapping);

    expect(Routing.generate).toHaveBeenCalledWith(route, {
      identifier: familyCode
    });
    expect(ajax).toHaveBeenCalledWith({
      url,
      method: 'POST',
      contentType: 'application/json',
      data:
        '{"mapping":{"color":{"franklinAttribute":{"code":"color","label":"Color","type":"text","summary":["white"]},"attribute":"color","status":"pending","exactMatchAttributeFromOtherFamily":"color","canCreateAttribute":false}}}'
    });
    expect(response).toBe(ajaxResponse);
  });
});
