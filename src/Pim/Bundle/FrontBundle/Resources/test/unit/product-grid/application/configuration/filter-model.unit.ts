import {FilterProvider} from 'pimfront/product-grid/application/configuration/filter-model';
import BooleanPropertyFilter from 'pimfront/product-grid/domain/model/filter/property/boolean';
import BooleanAttributeFilter from 'pimfront/product-grid/domain/model/filter/attribute/boolean';
import {Property, Attribute} from 'pimfront/product-grid/domain/model/field';
import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';
import All from 'pimfront/product-grid/domain/model/filter/operator/all';
import valueProvider from 'pimfront/product-grid/application/configuration/value';
import {Null} from 'pimfront/product-grid/domain/model/filter/value';

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
      model: 'a/model/path/to/a/property/filter',
      label: 'Status',
    },
    not_well_configured_field: {
      wow_not_good: 'a/model/path/to/a/property/filter',
    },
  },
  attribute: {
    pim_catalog_boolean: {
      model: 'a/model/path/to/an/attribute/filter',
    },
    not_well_configured_type: {
      wow_not_good: 'a/model/path/to/an/attribute/filter',
    },
  },
  operator: {
    ALL: 'an/operator/path',
  },
  value: ['a/value/provider/path'],
};

const moduleLoader = modulePath => {
  switch (modulePath) {
    case 'a/model/path/to/a/property/filter':
      return {
        default: BooleanPropertyFilter,
      };
    case 'a/model/path/to/an/attribute/filter':
      return {
        default: BooleanAttributeFilter,
      };
    case 'an/operator/path':
      return {
        default: All,
      };
    case 'a/value/provider/path':
      return {
        default: valueProvider,
      };
    default:
      break;
  }
};

let filterProvider = new FilterProvider(config, fetcherRegistry, moduleLoader);

describe('>>>APPLICATION --- config - filters', () => {
  test('It throw an exception for the given unknown property code', async () => {
    expect.assertions(1);

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
    try {
      await filterProvider.getEmptyFilter();
    } catch (error) {
      expect(error.message).toBe('The method getEmptyFilter expect a code as parameter');
    }
  });

  test('It provides me a filter for the given property code', async () => {
    expect.assertions(1);
    const statusProperty = Property.createFromProperty({identifier: 'enabled', label: 'Status'});

    const expectedStatus = BooleanPropertyFilter.createEmpty(statusProperty);

    const status = await filterProvider.getEmptyFilter('enabled');

    expect(status).toEqual(expectedStatus);
  });

  test('It provides me a filter for the given attribute code', async () => {
    expect.assertions(1);
    const refurbishedAttribute = Attribute.createFromAttribute({
      identifier: 'refurbished',
      type: 'pim_catalog_boolean',
    });

    const expectedRefurbished = BooleanAttributeFilter.createEmpty(refurbishedAttribute);

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

  test('It throw a missconfigured error if I have an error in my property configuration', async () => {
    expect.assertions(3);

    try {
      await filterProvider.getEmptyFilter('not_well_configured_field');
    } catch (error) {
      expect(error.message.includes('field')).toBe(true);
      expect(error.message.includes('not_well_configured_field')).toBe(true);
      expect(error.message.includes('model')).toBe(true);
    }
  });

  test('It throw an exception for the given unknown property code', async () => {
    expect.assertions(1);

    const unknownNormalizedFilter = NormalizedFilter.create({field: 'no existing filter', operator: '=', value: true});

    try {
      await filterProvider.getPopulatedFilter(unknownNormalizedFilter);
    } catch (error) {
      expect(error.message).toBe(
        'The property "no existing filter" is neither an attribute filter nor a property filter. Did you registered it well in the requirejs configuration?'
      );
    }
  });

  test('It returns an empty array if no codes are passed', async () => {
    expect.assertions(1);
    try {
      await filterProvider.getPopulatedFilter();
    } catch (error) {
      expect(error.message).toBe('The method getPopulatedFilter expect a valid NormalizedFilter');
    }
  });

  test('It provides me a filter for the given field code', async () => {
    expect.assertions(1);
    const statusProperty = Property.createFromProperty({identifier: 'enabled', label: 'Status'});
    const allOperator = All.create();
    const nullValue = Null.fromValue(null);

    const expectedStatus = BooleanPropertyFilter.create(statusProperty, allOperator, nullValue);

    const statusNormalizedFilter = NormalizedFilter.create({field: 'enabled', operator: 'ALL', value: null});

    const status = await filterProvider.getPopulatedFilter(statusNormalizedFilter);

    expect(status).toEqual(expectedStatus);
  });

  test('It provides me a filter for the given normalized attribute', async () => {
    expect.assertions(1);
    const refurbishedAttribute = Attribute.createFromAttribute({
      identifier: 'refurbished',
      type: 'pim_catalog_boolean',
    });
    const allOperator = All.create();
    const nullValue = Null.fromValue(null);

    const expectedRefurbished = BooleanAttributeFilter.create(refurbishedAttribute, allOperator, nullValue);

    const normalizedFilter = NormalizedFilter.create({field: 'refurbished', operator: 'ALL', value: null});

    const refurbished = await filterProvider.getPopulatedFilter(normalizedFilter);

    expect(refurbished).toEqual(expectedRefurbished);
  });

  test('It throw a missconfigured error if I have an error in my attribute configuration', async () => {
    expect.assertions(3);

    const normalizedFilter = NormalizedFilter.create({
      field: 'not_well_configured_attribute',
      operator: 'ALL',
      value: null,
    });

    try {
      await filterProvider.getPopulatedFilter(normalizedFilter);
    } catch (error) {
      expect(error.message.includes('attribute')).toBe(true);
      expect(error.message.includes('not_well_configured_type')).toBe(true);
      expect(error.message.includes('model')).toBe(true);
    }
  });

  test('It throw a missconfigured error if I have an error in my field configuration', async () => {
    expect.assertions(3);

    const normalizedFilter = NormalizedFilter.create({
      field: 'not_well_configured_field',
      operator: 'ALL',
      value: null,
    });
    try {
      await filterProvider.getPopulatedFilter(normalizedFilter);
    } catch (error) {
      expect(error.message.includes('field')).toBe(true);
      expect(error.message.includes('not_well_configured_field')).toBe(true);
      expect(error.message.includes('model')).toBe(true);
    }
  });
});
