import {
  redirectToProductGridFilteredByCategory,
  redirectToProductGridFilteredByFamily,
  redirectToProductGridFilteredByKeyIndicator,
} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/ProductGridRouter';

import DatagridState from 'pim/datagrid/state';

jest.mock('pim/datagrid/state');

beforeAll(() => {
  jest.spyOn(DatagridState, 'set');
});

afterEach(() => {
  DatagridState.set.mockClear();
});

const columnsWithDQI =
  'identifier,image,label,family,enabled,completeness,created,updated,complete_variant_products,success,data_quality_insights_score';

describe('ProductGridRouter', function () {
  describe('redirectToProductGridFilteredByFamily', function () {
    test('redirects to product grid filtered on a family', () => {
      redirectToProductGridFilteredByFamily('ecommerce', 'en_US', 'accessories');
      assertDatagridState(
        columnsWithDQI,
        's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[family][value][]=accessories&f[family][type]=in&f[category][value][treeId]=null&f[category][value][categoryId]=null&f[category][type]=1'
      );
    });
  });

  describe('redirectToProductGridFilteredByCategory', function () {
    test('Redirecs to product grid filtered on a category', () => {
      redirectToProductGridFilteredByCategory('ecommerce', 'en_US', '39', '5');
      assertDatagridState(
        columnsWithDQI,
        's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[category][value][treeId]=5&f[category][value][categoryId]=39&f[category][type]=1'
      );
    });
  });

  describe('redirectToProductGridFilteredByKeyIndicator', function () {
    test('redirects to product grid filtered on enrichment quality key indicator', () => {
      redirectToProductGridFilteredByKeyIndicator(
        'data_quality_insights_enrichment_quality',
        'ecommerce',
        'en_US',
        'product',
        null,
        null,
        null
      );
      assertDatagridState(
        columnsWithDQI,
        's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[data_quality_insights_enrichment_quality][value]=0&f[category][value][treeId]=null&f[category][value][categoryId]=null&f[category][type]=1'
      );
    });

    test('redirects to product grid filtered on enrichment quality key indicator and family', () => {
      redirectToProductGridFilteredByKeyIndicator(
        'data_quality_insights_enrichment_quality',
        'ecommerce',
        'en_US',
        'product',
        'accessories',
        null,
        null
      );
      assertDatagridState(
        columnsWithDQI,
        's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[family][value][]=accessories&f[family][type]=in&f[data_quality_insights_enrichment_quality][value]=0&f[category][value][treeId]=null&f[category][value][categoryId]=null&f[category][type]=1'
      );
    });

    test('redirects to product grid filtered on enrichment quality key indicator and category', () => {
      redirectToProductGridFilteredByKeyIndicator(
        'data_quality_insights_enrichment_quality',
        'ecommerce',
        'en_US',
        'product',
        null,
        '12',
        '4'
      );
      assertDatagridState(
        columnsWithDQI,
        's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[data_quality_insights_enrichment_quality][value]=0&f[category][value][treeId]=4&f[category][value][categoryId]=12&f[category][type]=1'
      );
    });
  });
});

const normalizeFilters = (filters: string) =>
  filters
    .split('&')
    .sort((a, b) => a.localeCompare(b))
    .join('&');

function assertDatagridState(expectedColumns: string, expectedFilters: string) {
  const {
    mock: {calls},
  } = DatagridState.set as jest.Mock;
  const [call1] = calls;

  expect(call1).not.toBeUndefined();

  const [gridType, {columns, filters, view, initialViewState, scope}] = call1;

  expect(gridType).toEqual('product-grid');

  expect(columns).toEqual(expectedColumns);
  expect(view).toBe(0);
  expect(scope).toBe('ecommerce');

  const normalizedExpectedFilters = normalizeFilters(expectedFilters);
  expect(normalizeFilters(filters)).toEqual(normalizedExpectedFilters);
  expect(normalizeFilters(initialViewState)).toEqual(normalizedExpectedFilters);
}
