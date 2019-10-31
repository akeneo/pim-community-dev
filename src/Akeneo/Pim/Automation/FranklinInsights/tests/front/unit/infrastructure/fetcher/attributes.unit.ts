import {getJSON} from 'jquery';
import {fetchAttributesByFamily} from '../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/attributes';
import {ALLOWED_ATTRIBUTE_TYPES} from '../../../../../Infrastructure/Symfony/Resources/public/react/domain/allowed-attribute-types';

const Routing = require('routing');

jest.mock('jquery');
jest.mock('routing');

const familyCode = 'headphones';
const route = 'pim_enrich_attribute_rest_index';

test('it fetches family attributes', async () => {
  const apiResponse = [
    {code: 'Aspect_Ratio', type: 'pim_catalog_text'},
    {code: 'Digital_Audio_Format', type: 'pim_catalog_text'}
  ];
  const expectedAttributes = {
    Aspect_Ratio: {code: 'Aspect_Ratio', type: 'pim_catalog_text'},
    Digital_Audio_Format: {code: 'Digital_Audio_Format', type: 'pim_catalog_text'}
  };

  getJSON.mockResolvedValue(apiResponse);

  const attributes = await fetchAttributesByFamily(familyCode);

  expect(Routing.generate).toHaveBeenCalledWith(route, {
    families: [familyCode],
    localizable: false,
    is_locale_specific: false,
    scopable: false,
    types: ALLOWED_ATTRIBUTE_TYPES,
    options: {
      limit: 1000
    }
  });

  expect(getJSON).toHaveBeenCalled();

  expect(attributes.Aspect_Ratio).toMatchObject(expectedAttributes.Aspect_Ratio);
});
