import '@testing-library/jest-dom/extend-expect';
import AppcuesOnboarding from '../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-onboarding';
import {getAppcuesAgent} from '../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-agent';

jest.mock('../../../../back/Infrastructure/Symfony/Resources/public/js/onboarding/appcues-agent');

const FeatureFlags = require('pim/feature-flags');
FeatureFlags.isEnabled.mockImplementation(() => true);

const UserContext = require('pim/user-context');
UserContext.get.mockImplementation((data: string) => {
  switch (data) {
    case 'username':
      return 'julia';
    case 'email':
      return 'julia@akeneo.com';
    case 'first_name':
      return 'julia';
    case 'last_name':
      return 'Stark';
    default:
      return data;
  }
});

const mockedAppcues = {
  identify: jest.fn(),
  page: jest.fn(),
  track: jest.fn(),
  on: jest.fn(),
  loadLaunchpad: jest.fn(),
};

beforeAll(() => {
  // @ts-ignore;
  getAppcuesAgent.mockResolvedValue(mockedAppcues);
});

beforeEach(() => {
  jest.clearAllMocks();
  jest.restoreAllMocks();
});

describe('Appcues init', () => {
  test('it set up appcues onboarding when appcues is initialized', async () => {
    // @ts-ignore;
    getAppcuesAgent.mockResolvedValue(mockedAppcues);

    await AppcuesOnboarding.init();

    expect(mockedAppcues.identify).toHaveBeenCalledWith('julia', {
      email: 'julia@akeneo.com',
      first_name: 'julia',
      last_name: 'Stark',
    });
    expect(mockedAppcues.identify).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.loadLaunchpad).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.on).toHaveBeenCalledTimes(3);
  });
});

describe('Random event', () => {
  test('a random event has been tracked', async () => {
    await AppcuesOnboarding.track('random:event');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('random:event');
  });
});

describe('Navigation', () => {
  test('a navigation entry has been clicked', async () => {
    await AppcuesOnboarding.track('navigation:entry:clicked', {
      code: 'pim-menu-activity',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Navigation entry "pim-menu-activity" clicked');
  });
});

describe('Product grid', () => {
  test('a product has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'product-grid',
      entityHint: 'product',
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
          identifier: 'PLGCHAELK001',
        },
      },
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
          identifier: 'BFGoodrich - Advantage T/A Sport',
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model "BFGoodrich - Advantage T/A Sport" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product selected');
  });

  test('all products has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:all-selected', {
      inputName: 'product-grid',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('All products are selected');
  });
});

describe('Export profile grid', () => {
  test('an export profile has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'export-profile-grid',
      entityHint: 'export profile',
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
          code: 'printers_amazon',
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Export profile "Printers for Amazon (weekly)" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Export profile selected');
  });
});

describe('Family grid', () => {
  test('a family has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:selected', {
      name: 'family-grid',
      entityHint: 'family',
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
          label: 'Tires',
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Family "Tires" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Family selected');
  });

  test('3 families has been selected', async () => {
    await AppcuesOnboarding.track('grid:item:number-selected', {
      inputName: 'family-grid',
      count: 3,
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('3 families selected in the grid');
  });
});

describe('Product grid views', () => {
  test('a view has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:view:selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View selected');
  });

  test('the "Furniture to Enrich" view has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:view:selected', {
      name: 'Furniture - To enrich',
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
      name: 'Furniture - To enrich',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('View "Furniture - To enrich" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('View saved');
  });
});

describe('Product grid columns', () => {
  test('a column has been added', async () => {
    await AppcuesOnboarding.track('product-grid:column:selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Column added in the product grid');
  });

  test('the column "Designer" has been added', async () => {
    await AppcuesOnboarding.track('product-grid:column:selected', {
      gridName: 'product-grid',
      column: 'designer',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Column "Designer" added in the product grid');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Column added in the product grid');
  });
});

describe('Product grid attribute groups', () => {
  test('an attribute group has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:attribute-group:selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });

  test('the attribute group "Content / Copy" has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:attribute-group:selected', {
      code: 'contentcopy',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group "Content / Copy" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });

  test('the attribute group "Specifications / Product Team" has been selected', async () => {
    await AppcuesOnboarding.track('product-grid:attribute-group:selected', {
      code: 'specifications',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group "Specifications / Product Team" selected');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute group selected in the product grid');
  });
});

describe('Product page', () => {
  test('the compare button has been clicked', async () => {
    await AppcuesOnboarding.track('product:form:compare-clicked');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Compare button clicked');
  });

  test("the product's locale switched to another locale", async () => {
    await AppcuesOnboarding.track('product:form:locale-switched', {
      context: 'base_product',
      localeCode: 'es_ES',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product\'s locale switched to "es_ES"');
  });

  test("the compare's locale switched to another locale", async () => {
    await AppcuesOnboarding.track('product:form:locale-switched', {
      context: 'copy_product',
      localeCode: 'fr_FR',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Compare\'s locale switched to "fr_FR"');
  });

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
      value: true,
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

  test('the product "Elka Peacock Armchair" has been saved', async () => {
    await AppcuesOnboarding.track('product:form:saved', {
      name: 'PLGCHAELK001',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product "Elka Peacock Armchair" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product saved');
  });

  test('the product "Faux leather tote" has been saved', async () => {
    await AppcuesOnboarding.track('product:form:saved', {
      name: 'PLG513725',
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

  test('the product model "BFGoodrich - Advantage T/A Sport" has been saved', async () => {
    await AppcuesOnboarding.track('product-model:form:saved', {
      code: 'BFGoodrich - Advantage T/A Sport',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model "BFGoodrich - Advantage T/A Sport" saved');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Product model saved');
  });

  test('a variant has been selected from a product model', async () => {
    await AppcuesOnboarding.track('product-model:form:variant-selected');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Variant selected from product model');
  });
});

describe('Bulk actions', () => {
  test('the bulk action button in the product grid has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:clicked', {
      name: 'product-edit',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Button "Bulk actions" in product grid clicked');
  });

  test('the bulk action button in the family grid has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:clicked', {
      name: 'family-edit',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Button "Bulk actions" in family grid clicked');
  });

  test('the bulk action step "Next" has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:action-step', {
      name: 'configure',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Clicked on "Next" after choosing a bulk action');
  });

  test('the bulk action step "Confirm" has been clicked', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:action-step', {
      name: 'validate',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Clicked on "Confirm" after configuring a bulk action');
  });

  test('the bulk action "Add attribute values" has been selected', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:item-chosen', {
      code: 'add_attribute_value',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Bulk action "Add attribute values" selected');
  });

  test('the bulk action "Set attributes requirements" has been selected', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:item-chosen', {
      code: 'set_requirements',
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
      codes: ['certifications', 'random_attribute'],
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Certifications" added in a bulk action');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute added in a bulk action');
  });

  test('the attribute "Certifications" has been added', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:attributes-added', {
      codes: ['food_standard', 'random_attribute'],
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Industry Standards" added in a bulk action');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute added in a bulk action');
  });

  test('from the family grid, the attribute "Photo printing" has been added', async () => {
    await AppcuesOnboarding.track('family-grid:mass-edit:attributes-added', {
      codes: ['photo_printing', 'random_attribute'],
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Photo printing" added in a bulk action');
  });

  test('from the product grid, the option "Vegan" has been added to the attribute "Certifications"', async () => {
    await AppcuesOnboarding.track('product-grid:mass-edit:attributes-added', {
      values: {
        certifications: {
          0: {
            data: ['vegan', 'random_option'],
          },
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith(
      'Option "Vegan" added from the attribute "Certifications" in a bulk action'
    );
  });

  test('from the product grid, the option "Red Tractor" has been added to the attribute "Industry Standards"', async () => {
    await AppcuesOnboarding.track('product-grid:mass-edit:attributes-added', {
      values: {
        food_standard: {
          0: {
            data: ['red_tractor', 'random_option'],
          },
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith(
      'Option "Red Tractor" added from the attribute "Industry Standards" in a bulk action'
    );
  });

  test('from the family grid, the information is required for Marketplaces channel', async () => {
    await AppcuesOnboarding.track('grid:mass-edit:requirements-checked', {
      actions: {
        0: {
          channel_code: 'marketplaces',
          is_required: true,
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('The information is required for Marketplaces channel');
  });
});

describe('Settings', () => {
  test('the attributes settings has been clicked', async () => {
    await AppcuesOnboarding.track('settings:attributes:clicked');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Settings: "Attributes" clicked');
  });

  test('the families settings has been clicked', async () => {
    await AppcuesOnboarding.track('settings:families:clicked');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Settings: "Families" clicked');
  });
});

describe('Attribute settings', () => {
  test('an attribute has been created', async () => {
    await AppcuesOnboarding.track('attribute:create:type-selected', {
      type: 'boolean',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute of type "boolean" created');
  });

  test('on attribute form, a field has been changed', async () => {
    await AppcuesOnboarding.track('common:form:value-changed', {
      code: 'pim-attribute',
      name: 'description',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('On attribute form, the value of field "description" changed');
  });

  test('on attribute form, a field has been changed', async () => {
    await AppcuesOnboarding.track('translation:form:value-changed', {
      code: 'pim-attribute',
      localeCode: 'en_US',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('On attribute form, the translation label of "en_US" changed');
  });

  test('the attribute create form has been saved', async () => {
    await AppcuesOnboarding.track('common:form:saved', {
      code: 'pim-attribute-create',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Create attribute form saved');
  });
});

describe('Family settings', () => {
  test('the attribute "Meta title" has been added as a family variant', async () => {
    await AppcuesOnboarding.track('family:variant:attribute-set', {
      codes: ['meta_title', 'random_attribute'],
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Meta title" added as family variant');
  });

  test('the attribute "Winter Designed Tire" has been added as a family variant', async () => {
    await AppcuesOnboarding.track('family:variant:attribute-remove', {
      codes: ['winter_designed_tire', 'random_attribute'],
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Attribute "Winter Designed Tire" added as family variant');
  });

  test('a family variant has been saved', async () => {
    await AppcuesOnboarding.track('family:variant:saved');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Family variant saved');
  });

  test('the tab "Variants" has been opened', async () => {
    await AppcuesOnboarding.track('family:edit:variant-selected', {
      code: 'pim-family-edit-form-variant',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Tab "Variants" selected in family edit form');
  });
});

describe('Export profile', () => {
  test('an export profile has been opened to be editing', async () => {
    await AppcuesOnboarding.track('form:edit:opened', {
      code: 'pim-job-instance-xlsx-product-export-edit',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Edit export profile');
  });

  test('an export profile has been opened to be editing', async () => {
    await AppcuesOnboarding.track('form:edit:opened', {
      code: 'pim-job-instance-xlsx-product-export-edit',
      model: {
        attributes: {
          code: 'printers_amazon',
        },
      },
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(2);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Edit export profile "Printers for Amazon (weekly)"');
    expect(mockedAppcues.track).toHaveBeenCalledWith('Edit export profile');
  });

  test('on export profile edit, content tab has been opened', async () => {
    await AppcuesOnboarding.track('export-profile:product:content-tab-opened', {
      code: 'pim-job-instance-xlsx-product-export-edit-content',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Content tab opened on edit export profile product');
  });

  test('on export profile edit, the attribute "Automatic Two-Sided Printing" has been added in the content', async () => {
    await AppcuesOnboarding.track('export-profile:product:attribute-added', {
      column: 'automatic_two_sided_printing, random_attribute',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith(
      'Attribute "Automatic Two-Sided Printing" added in the content of the export profile'
    );
  });

  test('the selection of attributes in the content of an export profile has been applied', async () => {
    await AppcuesOnboarding.track('export-profile:product:attribute-applied');
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith(
      'Selection of attributes in the content of the export profile applied'
    );
  });

  test('the edit export profile "Printers for Amazon (weekly)" has been saved', async () => {
    await AppcuesOnboarding.track('job-instance:form-edit:saved', {
      code: 'printers_amazon',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Edit export profile "Printers for Amazon (weekly)" saved');
  });

  test('the export profile "Printers for Amazon (weekly)" has been launched', async () => {
    await AppcuesOnboarding.track('job-instance:export:launched', {
      url: 'http://test/spread/export/printers_amazon',
    });
    expect(mockedAppcues.track).toHaveBeenCalledTimes(1);
    expect(mockedAppcues.track).toHaveBeenCalledWith('Export profile "Printers for Amazon (weekly)" launched');
  });
});
