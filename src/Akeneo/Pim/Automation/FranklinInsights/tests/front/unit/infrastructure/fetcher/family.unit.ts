import {getJSON} from 'jquery';
import {fetchFamilyLabels} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/family';
import {generateUrl} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator';

jest.mock('jquery');
jest.mock('../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/service/url-generator');

beforeEach(() => {
  jest.resetModules();
});

describe('Infrastructure > Fetcher > Family', () => {
  test('It gets family labels by family code', async () => {
    expect(true).toBe(true);
    const familyCode = 'myFamilyCode';
    const labels = {
      en_US: 'My Family',
      fr_FR: 'Ma Famille'
    };
    const url = `/configuration/family/rest/${familyCode}`;
    const apiResponse = {
      labels
    };

    generateUrl.mockReturnValue(url);
    getJSON.mockResolvedValue(apiResponse);

    const response = await fetchFamilyLabels(familyCode);

    expect(generateUrl).toHaveBeenCalledWith('pim_enrich_family_rest_get', {
      identifier: familyCode
    });
    expect(getJSON).toHaveBeenCalledWith(url);
    expect(response).toBe(labels);
  });
});
