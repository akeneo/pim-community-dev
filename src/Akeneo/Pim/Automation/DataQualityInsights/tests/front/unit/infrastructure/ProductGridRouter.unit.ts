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

const columnsWithDQI =
  'identifier,image,label,family,enabled,completeness,created,updated,complete_variant_products,success,data_quality_insights_score';

test('Redirect to product grid filtered on a family', () => {
  redirectToProductGridFilteredByFamily('ecommerce', 'en_US', 'accessories');
  assertDatagridState(
    columnsWithDQI,
    's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[family][value][]=accessories&f[family][type]=in'
  );
});

test('Redirect to product grid filtered on a category', () => {
  redirectToProductGridFilteredByCategory('ecommerce', 'en_US', '39', '5');
  assertDatagridState(
    columnsWithDQI,
    's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[category][value][treeId]=5&f[category][value][categoryId]=39&f[category][type]=1'
  );
});

test('Redirect to product grid filtered on enrichment quality key indicator', () => {
  redirectToProductGridFilteredByKeyIndicator(
    'data_quality_insights_enrichment_quality',
    'ecommerce',
    'en_US',
    null,
    null,
    null
  );
  assertDatagridState(
    columnsWithDQI,
    's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[data_quality_insights_enrichment_quality][value]=0'
  );
});

test('Redirect to product grid filtered on enrichment quality key indicator and family', () => {
  redirectToProductGridFilteredByKeyIndicator(
    'data_quality_insights_enrichment_quality',
    'ecommerce',
    'en_US',
    'accessories',
    null,
    null
  );
  assertDatagridState(
    columnsWithDQI,
    's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[family][value][]=accessories&f[family][type]=in&f[data_quality_insights_enrichment_quality][value]=0'
  );
});

test('Redirect to product grid filtered on enrichment quality key indicator and category', () => {
  redirectToProductGridFilteredByKeyIndicator(
    'data_quality_insights_enrichment_quality',
    'ecommerce',
    'en_US',
    null,
    '12',
    '4'
  );
  assertDatagridState(
    columnsWithDQI,
    's[updated]=1&f[scope][value]=ecommerce&f[entity_type][value]=product&t=product-grid&f[category][value][treeId]=4&f[category][value][categoryId]=12&f[category][type]=1&f[data_quality_insights_enrichment_quality][value]=0'
  );
});

function assertDatagridState(columns: string, filters: string) {
  expect(DatagridState.set).toHaveBeenCalledWith('product-grid', {
    columns: columns,
    filters: filters,
    view: 0,
    initialViewState: filters,
    scope: 'ecommerce',
  });
}
