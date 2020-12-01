const Router = require('pim/router');
const DatagridState = require('pim/datagrid/state');

export const redirectToProductGridFilteredByFamily = (channelCode: string, localeCode: string, familyCode: string) => {
  const gridFilters = buildFilters(channelCode, familyCode, null, null, null);
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

export const redirectToProductGridFilteredByCategory = (
  channelCode: string,
  localeCode: string,
  categoryId: string,
  rootCategoryId: string
) => {
  const gridFilters = buildFilters(channelCode, null, categoryId, rootCategoryId, null);
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

export const redirectToProductGridFilteredByKeyIndicator = (
  keyIndicator: string,
  channelCode: string,
  localeCode: string,
  familyCode: string | null,
  categoryId: string | null,
  rootCategoryId: string | null
) => {
  const gridFilters = buildFilters(channelCode, familyCode, categoryId, rootCategoryId, keyIndicator);
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

const buildFilters = (
  channelCode: string,
  familyCode: string | null,
  categoryId: string | null,
  rootCategoryId: string | null,
  keyIndicator: string | null
) => {
  let filters = ['s[updated]=1', `f[scope][value]=${channelCode}`, 'f[entity_type][value]=product', 't=product-grid'];
  if (familyCode) {
    filters = filters.concat([`f[family][value][]=${familyCode}`, 'f[family][type]=in']);
  }
  if (categoryId) {
    filters = filters.concat([
      `f[category][value][treeId]=${rootCategoryId}`,
      `f[category][value][categoryId]=${categoryId}`,
      'f[category][type]=1',
    ]);
  }
  if (keyIndicator) {
    filters = filters.concat([`f[${keyIndicator}][value]=0`]);
  }

  return filters.join('&');
};

const redirectToFilteredProductGrid = (
  channelCode: string,
  localeCode: string,
  gridFilters: string
) => {
  const productGridColumns = getDefaultProductGridColumns();
  DatagridState.set('product-grid', {
    columns: productGridColumns.join(','),
    filters: gridFilters,
    view: '0',
    initialViewState: '',
    scope: channelCode,
  });

  window.location.href = '#' + Router.generate('pim_enrich_product_index', {dataLocale: localeCode});
};

const getDefaultProductGridColumns = () => {
  return 'identifier,image,label,family,enabled,data_quality_insights_score,completeness,created,updated,complete_variant_products'.split(
    ','
  );
};
