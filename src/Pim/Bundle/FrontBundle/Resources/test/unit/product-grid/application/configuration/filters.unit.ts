import {Filters} from 'pimfront/product-grid/application/configuration/filters';
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
  fetch: () => {
    return Promise.resolve({
      identifier: 'refurbished',
      type: 'pim_catalog_boolean',
    });
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
  },
  attribute: {
    pim_catalog_boolean: {
      model: 'pimfront/product-grid/domain/model/filter/attribute/boolean',
      view: 'pimfront/product-grid/application/component/filter/boolean',
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

describe('>>>APPLICATION --- config - filters', () => {
  test('It throw an error for the given unknown property codes', async () => {
    expect.assertions(1);
    const filters = new Filters(config, fetcherRegistry, moduleLoader);

    try {
      await filters.getEmptyFilterModelsFromCodes(['no existing filter']);
    } catch (err) {
      expect(err.message).toBe(
        'The field "no existing filter" is neither an attribute filter nor a property filter. Did you registered it well in the requirejs configuration?'
      );
    }
  });

  test('It returns an empty array if no codes are passed', async () => {
    expect.assertions(1);
    const filters = new Filters(config, fetcherRegistry, moduleLoader);

    const actualFilters = await filters.getEmptyFilterModelsFromCodes([]);

    expect(actualFilters).toEqual([]);
  });

  test('It provides me a list of filters for the given property and attribute codes', async () => {
    expect.assertions(2);
    const statusProperty = Property.createFromProperty({identifier: 'enabled', label: 'Status'});
    const refurbishedAttribute = Attribute.createFromAttribute({
      identifier: 'refurbished',
      type: 'pim_catalog_boolean',
    });

    const expectedStatus = StatusFilter.createEmptyFromProperty(statusProperty);
    const expectedRefurbished = BooleanFilter.createEmptyFromAttribute(refurbishedAttribute);

    const filters = new Filters(config, fetcherRegistry, moduleLoader);

    const [status, refurbished] = await filters.getEmptyFilterModelsFromCodes(['enabled', 'refurbished']);

    expect(status).toEqual(expectedStatus);
    expect(refurbished).toEqual(expectedRefurbished);
  });
});
