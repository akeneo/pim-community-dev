import '@testing-library/jest-dom/extend-expect';
import AppcuesOnboarding from "../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-onboarding";
import {getAppcuesAgent} from '../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-agent';

jest.mock('../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-agent');

const mockedAppcues = {
  track: jest.fn(),
};

beforeAll(() => {
  getAppcuesAgent.mockResolvedValue(mockedAppcues);
});

beforeEach(() => {
  jest.clearAllMocks();
  jest.restoreAllMocks();
});

describe('Product grid views', () => {
  test('a view has been saved', async () => {
    await AppcuesOnboarding.track('product-grid:view:saved', {
      name: 'toto'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View saved');
  })


  test('the "Furniture to Enrich" view has been saved', async () => {
    await AppcuesOnboarding.track('product-grid:view:saved', {
      name: 'Furniture - To enrich'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View "Furniture - To enrich" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('View saved');
  })
})

/*describe('Appcues onboarding', () => {
  beforeAll(() => {
    window.Appcues = {
      track: jest.fn(),
    };
  })
  afterAll(() => {
    jest.restoreAllMocks()
  })

  test('it tracks when a view is saved', async () => {
    AppcuesOnboarding.track('product-grid:view:saved');
    expect(window.Appcues.track).toHaveBeenCalledWith('View saved');

    AppcuesOnboarding.track('product-grid:view:saved', {name: 'Furniture - To enrich'});

    expect(window.Appcues.track).toHaveBeenCalledWith('View Furniture - To enrich saved');
  });

  /*test('it tracks when a view is selected', async () => {
    AppcuesOnboarding.track('product-grid:view:selected');
    expect(window.Appcues.track).toHaveBeenCalledWith('View selected');

    AppcuesOnboarding.track('product-grid:view:selected', {name: 'Furniture - To enrich'});
    expect(window.Appcues.track).toHaveBeenCalledWith('View Furniture - To enrich selected');
  });

  test('it tracks when a column is added in the product grid', async () => {
    AppcuesOnboarding.track('product-grid:column:selected');
    expect(window.Appcues.track).toHaveBeenCalledWith('Column added in the product grid');

    AppcuesOnboarding.track('product-grid:column:selected', {gridName: 'product-grid', column: 'designer'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Column "Designer" added in the product grid');
  });

  test('it tracks when a product is selected in the product grid', async () => {
    AppcuesOnboarding.track('grid:item:selected', {name: 'product-grid', entityHint: 'product', model: {attributes: {identifier: 'PLGCHAELK001'}}});
    expect(window.Appcues.track).toHaveBeenCalledWith('Product "Elka Peacock Armchair" selected');

    AppcuesOnboarding.track('grid:item:selected', {name: 'product-grid', entityHint: 'product', model: {attributes: {identifier: 'BFGoodrich - Advantage T/A Sport'}}});
    expect(window.Appcues.track).toHaveBeenCalledWith('Product model "BFGoodrich - Advantage T/A Sport" selected');

    AppcuesOnboarding.track('grid:item:selected', {name: 'product-grid', entityHint: 'product'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Product selected');
  });

  test('it tracks when an export profile is selected in the export profile grid', async () => {
    AppcuesOnboarding.track('grid:item:selected', {name: 'export-profile-grid', entityHint: 'export profile', model: {attributes: {code: 'printers_amazon'}}});
    expect(window.Appcues.track).toHaveBeenCalledWith('Export profile "Printers for Amazon (weekly)" selected');

    AppcuesOnboarding.track('grid:item:selected', {name: 'export-profile-grid', entityHint: 'export profile'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Export profile selected');
  });

  test('it tracks when a family is selected in the family grid', async () => {
    AppcuesOnboarding.track('grid:item:selected', {name: 'family-grid', entityHint: 'family', model: {attributes: {label: 'Tires'}}});
    expect(window.Appcues.track).toHaveBeenCalledWith('Family "Tires" selected');

    AppcuesOnboarding.track('grid:item:selected', {name: 'family-grid', entityHint: 'family'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Family selected');
  });

  test('it tracks when the completeness badge in the product page is opened', async () => {
    AppcuesOnboarding.track('product-grid:completeness:opened', {name: 'PLGCHAELK001'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Completeness badge for product "Elka Peacock Armchair" opened');

    AppcuesOnboarding.track('product-grid:completeness:opened');
    expect(window.Appcues.track).toHaveBeenCalledWith('Completeness badge opened in product edit form');
  });

  test('it tracks when an attribute group is selected in the product grid', async () => {
    AppcuesOnboarding.track('product-grid:attribute-group:selected', {code: 'contentcopy'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Attribute group "Content / Copy" selected');

    AppcuesOnboarding.track('product-grid:attribute-group:selected', {code: 'specifications'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Attribute group "Specifications / Product Team" selected');

    AppcuesOnboarding.track('product-grid:attribute-group:selected');
    expect(window.Appcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });

  test('it tracks when an attribute is filled in the product edit form', async () => {
    AppcuesOnboarding.track('product:attribute-value:updated', {attribute: 'winter_designed_tire', value: true});
    expect(window.Appcues.track).toHaveBeenCalledWith('Attribute "Winter designed Tire" changed to Yes value');

    AppcuesOnboarding.track('product:attribute-value:updated', {attribute: 'designer'});
    expect(window.Appcues.track).toHaveBeenCalledWith('Attribute "designer" filled in product edit form');
  });
});*/
