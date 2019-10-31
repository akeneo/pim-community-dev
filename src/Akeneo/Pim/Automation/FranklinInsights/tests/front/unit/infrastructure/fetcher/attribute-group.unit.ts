import {generateUrl} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator';
import {ajax} from 'jquery';
import {search} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/attribute-group';

jest.mock('jquery');
jest.mock('../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator');

beforeEach(() => {
  jest.resetModules();
});

describe('Infrastructure > Fetcher > Attribute group', () => {
  test('It gets attribute groups by codes', async () => {
    const attributeGroupCodes = ['marketing'];
    const url = '/rest/attribute-group/';
    const apiResponse = {
      marketing: {
        code: 'marketing',
        sort_order: 10,
        attributes: ['sku', 'name', 'description', 'response_time', 'release_date', 'price'],
        labels: {
          en_US: 'Marketing',
          fr_FR: 'Marketing'
        }
      }
    };

    generateUrl.mockReturnValue(url);
    ajax.mockResolvedValue(apiResponse);

    const response = await search(attributeGroupCodes);

    expect(generateUrl).toHaveBeenCalledWith('pim_enrich_attributegroup_rest_search', {
      options: {
        limit: -1,
        identifiers: attributeGroupCodes
      }
    });
    expect(ajax).toHaveBeenCalledWith({
      dataType: 'json',
      method: 'POST',
      url
    });
    expect(response).toBe(apiResponse);
  });

  test('It returns any result if there is no code passed as argument', async () => {
    const url = '/rest/attribute-group/';
    const apiResponse = {};

    generateUrl.mockReturnValue(url);
    ajax.mockResolvedValue(apiResponse);

    const response = await search();

    expect(generateUrl).toHaveBeenCalledWith('pim_enrich_attributegroup_rest_search', {
      options: {
        limit: -1,
        identifiers: []
      }
    });
    expect(ajax).toHaveBeenCalledWith({
      dataType: 'json',
      method: 'POST',
      url
    });
    expect(response).toBe(apiResponse);
  });
});
