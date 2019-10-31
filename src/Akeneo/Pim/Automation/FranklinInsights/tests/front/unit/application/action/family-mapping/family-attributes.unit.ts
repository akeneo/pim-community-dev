import {
  fetchedFamilyAttributesSuccess,
  fetchedFamilyAttributesFail,
  fetchAttributes
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/family-attributes';

jest.mock('../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/attributes');
import {fetchAttributesByFamily} from '../../../../../../Infrastructure/Symfony/Resources/public/react/infrastructure/fetcher/attributes';

it('fetches attributes', async () => {
  const dispatch = jest.fn();

  const fetcherResponse = {
    Aspect_Ratio: {code: 'Aspect_Ratio', type: 'pim_catalog_text'},
    Digital_Audio_Format: {code: 'Digital_Audio_Format', type: 'pim_catalog_text'}
  };

  fetchAttributesByFamily.mockResolvedValue(fetcherResponse);

  const promise = await fetchAttributes('headphones');
  expect(typeof promise).toBe('function');
  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith(fetchedFamilyAttributesSuccess(fetcherResponse));
});

it('fails to create an attribute', async () => {
  const dispatch = jest.fn();
  fetchAttributesByFamily.mockReturnValue(Promise.reject());

  const promise = fetchAttributes('headphones');
  expect(typeof promise).toBe('function');

  await promise(dispatch);

  expect(dispatch).toHaveBeenCalledWith(fetchedFamilyAttributesFail());
});
