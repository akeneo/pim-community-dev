import {followImageAttributeRecommendation} from "@akeneo-pim-ee/data-quality-insights/src/application";
import {aFamily, anAttribute, aProduct} from "../../../utils/provider";

const router = require('pim/router');

jest.mock('pim/router')

describe('followImageAttributeRecommendation', () => {
  const mockRedirectToRoute = jest.fn();

  beforeAll(() => {
    router.redirectToRoute = mockRedirectToRoute;
  })

  beforeEach(() =>{
    jest.resetAllMocks();
    localStorage.clear();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  })

  test('it redirects to the attribute on PEF when the image-attribute is empty', () => {
    const family = aFamily('a_family');
    const product = aProduct();
    const attribute = 'an_image_attribute';

    followImageAttributeRecommendation(attribute, product, family);

    expect(mockRedirectToRoute).not.toBeCalled();
  });

  test('it redirects to the asset collection edition when there is no asset in the collection', () => {
    const family = aFamily('a_family', 4321, {}, [
      anAttribute(),
      anAttribute('an_asset_collection_attribute', 666, 'pim_catalog_asset_collection'),
    ]);
    const product = aProduct();
    const attribute = 'an_asset_collection_attribute';

    followImageAttributeRecommendation(attribute, product, family);

    expect(mockRedirectToRoute).toBeCalledWith('akeneo_asset_manager_asset_family_index');
    expect(localStorage.getItem('akeneo.asset_manager.grid.current_asset_family')).toBe('a_reference_data');
  });
});
