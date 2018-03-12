import {FilterProvider, Missconfiguration} from 'pimfront/product-grid/application/configuration/filter-provider';
import StatusFilter from 'pimfront/product-grid/domain/model/filter/property/status';
import BooleanFilter from 'pimfront/product-grid/domain/model/filter/attribute/boolean';
import {Property, Attribute} from 'pimfront/product-grid/domain/model/field';

jest.mock('pimenrich/js/fetcher/fetcher-registry', () => ({}));

const attributeFetcher = {
  fetchByIdentifiers: () => {
    return Promise.resolve([
      {
        identifier: 'refurbished',
      },
    ]);
  },
  fetch: (code: string) => {
    switch (code) {
      case 'refurbished':
        return Promise.resolve({
          identifier: 'refurbished',
          type: 'pim_catalog_boolean',
        });
      case 'not_well_configured_attribute':
        return Promise.resolve({
          identifier: 'not_well_configured_attribute',
          type: 'not_well_configured_type',
        });
      default:
        return Promise.reject();
    }
  },
};

const fetcherRegistry = {
  getFetcher: () => attributeFetcher,
};

const config = {
  property: {
    enabled: {
      model: 'pimfront/product-grid/domain/model/filter/property/status',
      view: 'pimfront/product-grid/application/component/filter/boolean',
      label: 'Status',
    },
    not_well_configured_field: {
      wow_not_good: 'pimfront/product-grid/domain/model/filter/property/status',
    },
  },
  attribute: {
    pim_catalog_boolean: {
      model: 'pimfront/product-grid/domain/model/filter/attribute/boolean',
    },
    not_well_configured_type: {
      wow_not_good: 'pimfront/product-grid/domain/model/filter/attribute/boolean',
    },
  },
};

const moduleLoader = modulePath => {
  switch (modulePath) {
    case 'pimfront/product-grid/domain/model/filter/property/status':
      return {
        default: StatusFilter,
      };
    case 'pimfront/product-grid/domain/model/filter/attribute/boolean':
      return {
        default: BooleanFilter,
      };
    default:
      break;
  }
};

let filterProvider = new FilterProvider(config, fetcherRegistry, moduleLoader);

describe('>>>APPLICATION --- config - filters', () => {
  test('It return null for the given unknown property code', async () => {
    expect.assertions(1);
    const filters = new FilterProvider(config, fetcherRegistry, moduleLoader);

    try {
      await filterProvider.getEmptyFilter('no existing filter');
    } catch (error) {
      expect(error.message).toBe(
        'The property "no existing filter" is neither an attribute filter nor a property filter. Did you registered it well in the requirejs configuration?'
      );
    }
  });

  test('It returns an empty array if no codes are passed', async () => {
    expect.assertions(1);
    const filters = new FilterProvider(config, fetcherRegistry, moduleLoader);
    try {
      await filterProvider.getEmptyFilter();
    } catch (error) {
      expect(error.message).toBe('The method getFilter expect a code as parameter');
    }
  });

  test('It provides me a filter for the given field code', async () => {
    expect.assertions(1);
    const statusProperty = Property.createFromProperty({identifier: 'enabled', label: 'Status'});

    const expectedStatus = StatusFilter.createEmpty(statusProperty);

    const status = await filterProvider.getEmptyFilter('enabled');

    expect(status).toEqual(expectedStatus);
  });

  test('It provides me a filter for the given attribute code', async () => {
    expect.assertions(1);
    const refurbishedAttribute = Attribute.createFromAttribute({
      identifier: 'refurbished',
      type: 'pim_catalog_boolean',
    });

    const expectedRefurbished = BooleanFilter.createEmpty(refurbishedAttribute);

    const refurbished = await filterProvider.getEmptyFilter('refurbished');

    expect(refurbished).toEqual(expectedRefurbished);
  });

  test('It throw a missconfigured error if I have an error in my attribute configuration', async () => {
    expect.assertions(3);

    try {
      await filterProvider.getEmptyFilter('not_well_configured_attribute');
    } catch (error) {
      expect(error.message.includes('attribute')).toBe(true);
      expect(error.message.includes('not_well_configured_type')).toBe(true);
      expect(error.message.includes('model')).toBe(true);
    }
  });

  test('It throw a missconfigured error if I have an error in my field configuration', async () => {
    expect.assertions(3);

    try {
      await filterProvider.getEmptyFilter('not_well_configured_field');
    } catch (error) {
      expect(error.message.includes('field')).toBe(true);
      expect(error.message.includes('not_well_configured_field')).toBe(true);
      expect(error.message.includes('model')).toBe(true);
    }
  });
});
