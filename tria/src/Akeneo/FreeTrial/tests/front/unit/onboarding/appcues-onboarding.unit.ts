import '@testing-library/jest-dom/extend-expect';
import AppcuesOnboarding from "../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-onboarding";
import {getAppcuesAgent} from '../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-agent';

jest.mock('../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-agent');

const FeatureFlags = require('pim/feature-flags');
FeatureFlags.isEnabled.mockImplementation(() => true);

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

describe('Product grid', () => {
  test('a product has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'product-grid',
      entityHint: 'product'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product selected');
  });

  test('the product "Elka Peacock Armchair" has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'product-grid',
      entityHint: 'product',
      model: {
        attributes: {
          identifier: 'PLGCHAELK001'
        }
      }
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product "Elka Peacock Armchair" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product selected');
  });

  test('the product model "Product model "BFGoodrich - Advantage T/A Sport" selected" has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'product-grid',
      entityHint: 'product',
      model: {
        attributes: {
          identifier: 'BFGoodrich - Advantage T/A Sport'
        }
      }
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model "BFGoodrich - Advantage T/A Sport" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product selected');
  });

  test('all products has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:all-selected', {
      inputName: 'product-grid'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('All products are selected');
  });
})

describe('Export profile grid', () => {
  test('an export profile has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'export-profile-grid',
      entityHint: 'export profile'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Export profile selected');
  });

  test('the export profile "Printers for Amazon (weekly)" has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'export-profile-grid',
      entityHint: 'export profile',
      model: {
        attributes: {
          code: 'printers_amazon'
        }
      }
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Export profile "Printers for Amazon (weekly)" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Export profile selected');
  });
})

describe('Family grid', () => {
  test('a family has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'family-grid',
      entityHint: 'family'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Family selected');
  });

  test('the family "Tires" has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'family-grid',
      entityHint: 'family',
      model: {
        attributes: {
          label: 'Tires'
        }
      }
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Family "Tires" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Family selected');
  });
})

describe('Product grid views', () => {
  test('a view has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:view:selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View selected');
  });

  test('the "Furniture to Enrich" view has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:view:selected', {
      name: 'Furniture - To enrich'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View "Furniture - To enrich" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('View selected');
  });

  test('a view has been saved', async () => {
    await AppcuesOnboarding.track('product-grid:view:saved');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View saved');
  });

  test('the "Furniture to Enrich" view has been saved', async () => {
    await AppcuesOnboarding.track('product-grid:view:saved', {
      name: 'Furniture - To enrich'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View "Furniture - To enrich" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('View saved');
  });
})

describe('Product grid columns', () => {
  test('a column has been added', async () => {
    await AppcuesOnboarding.track('product-grid:column:selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Column added in the product grid');
  });

  test('the column "Designer" has been added', async () => {
    await AppcuesOnboarding.track('product-grid:column:selected', {
      gridName: 'product-grid',
      column: 'designer'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Column "Designer" added in the product grid');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Column added in the product grid');
  });
})

describe('Product grid attribute groups', () => {
  test('an attribute group has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:attribute-group:selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });

  test('the attribute group "Content / Copy" has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:attribute-group:selected', {
      code: 'contentcopy'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group "Content / Copy" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });

  test('the attribute group "Specifications / Product Team" has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:attribute-group:selected', {
      code: 'specifications'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group "Specifications / Product Team" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });
})

describe('Product page', () => {
  test('a completeness badge has been opened', async () => {
    await AppcuesOnboarding.track('product-grid:completeness:opened');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Completeness badge opened in product edit form');
  });

  test('the completeness badge for product "Elka Peacock Armchair" has been opened', async () => {
    await AppcuesOnboarding.track('product-grid:completeness:opened', {
      name: 'PLGCHAELK001',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Completeness badge for product "Elka Peacock Armchair" opened');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Completeness badge opened in product edit form');
  });

  test('the attribute "designer" has been filled', async () => {
    await AppcuesOnboarding.track('product:attribute-value:updated', {
      attribute: 'designer',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "designer" filled in product edit form');
  });

  test('the attribute "Winter designed Tire" has been filled', async () => {
    await AppcuesOnboarding.track('product:attribute-value:updated', {
      attribute: 'winter_designed_tire',
      value: true
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Winter designed Tire" changed to Yes value');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "winter_designed_tire" filled in product edit form');
  });

  test('a product has been saved', async () => {
    await AppcuesOnboarding.track('product:form:saved');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product saved');
  });

  test('the product "Elka Peacock Armchair" has been savec', async () => {
    await AppcuesOnboarding.track('product:form:saved', {
      name: 'PLGCHAELK001'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product "Elka Peacock Armchair" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product saved');
  });

  test('the product "Faux leather tote" has been savec', async () => {
    await AppcuesOnboarding.track('product:form:saved', {
      name: 'PLG513725'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product "Faux leather tote" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product saved');
  });

  test('a product model has been saved', async () => {
    await AppcuesOnboarding.track('product-model:form:saved');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model saved');
  });

  test('the product model "BFGoodrich - Advantage T/A Sport" has been savec', async () => {
    await AppcuesOnboarding.track('product-model:form:saved', {
      code: 'BFGoodrich - Advantage T/A Sport'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model "BFGoodrich - Advantage T/A Sport" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model saved');
  });
})

describe('Bulk actions', () => {
  test('the bulk action button in the product grid has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:clicked', {
      name: 'product-edit'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Button "Bulk actions" in product grid clicked');
  });

  test('the bulk action button in the family grid has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:clicked', {
      name: 'family-edit'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Button "Bulk actions" in family grid clicked');
  });

  test('the bulk action step "Next" has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:action-step', {
      name: 'configure'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Clicked on "Next" after choosing a bulk action');
  });

  test('the bulk action step "Confirm" has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:action-step', {
      name: 'validate'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Clicked on "Confirm" after configuring a bulk action');
  });

  test('the bulk action "Add attribute values" has been selected', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:item-chosen', {
      code: 'add_attribute_value'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Bulk action "Add attribute values" selected');
  });

  test('the bulk action "Set attributes requirements" has been selected', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:item-chosen', {
      code: 'set_requirements'
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Bulk action "Set attributes requirements" selected');
  });

  test('an attribute has been added', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:attributes-added');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute added in a bulk action');
  });

  test('the attribute "Certifications" has been added', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:attributes-added', {
      codes: ['certifications', 'random_attribute']
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Certifications" added in a bulk action');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute added in a bulk action');
  });

  test('the attribute "Certifications" has been added', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:attributes-added', {
      codes: ['food_standard', 'random_attribute']
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Industry Standards" added in a bulk action');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute added in a bulk action');
  });

  test('from the family grid, the attribute "Photo printing" has been added', async () => {
    await AppcuesOnboarding.track('family-grid:mass-edit:attributes-added', {
      codes: ['photo_printing', 'random_attribute']
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Photo printing" added in a bulk action');
  });

  /*test('from the product grid, the option "Vegan" has been added to the attribute "Certifications"', async () => {
    await AppcuesOnboarding.track('product-grid:mass-edit:attributes-added', {
      values: ['certifications']['0']['data']
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Photo printing" added in a bulk action');
  });*/
})
