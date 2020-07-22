const Router = require('pim/router');
const DatagridState = require('pim/datagrid/state');

const PRODUCT_GRID_CONSISTENCY_COLUMN = 'data_quality_insights_consistency';
const PRODUCT_GRID_ENRICHMENT_COLUMN = 'data_quality_insights_enrichment';

export const redirectToProductGridFilteredByFamily = (channelCode: string, localeCode: string, familyCode: string) => {
  const gridFilters = `s[updated]=1&f[family][value][]=${familyCode}&f[family][type]=in&f[scope][value]=${channelCode}&t=product-grid`;
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

export const redirectToProductGridFilteredByCategory = (channelCode: string, localeCode: string, categoryId: string, rootCategoryId: string) => {
  const gridFilters = `s[updated]=1&f[category][value][treeId]=${rootCategoryId}&f[category][value][categoryId]=${categoryId}&f[category][type]=1&f[scope][value]=${channelCode}&t=product-grid`;
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

const redirectToFilteredProductGrid = (channelCode: string, localeCode: string, gridFilters: string) => {
  const productGridColumns = addAxisColumnsToTheProductGrid();
  DatagridState.set('product-grid', {
    columns: productGridColumns.join(','),
    filters: gridFilters,
    view: '0',
    initialViewState: '',
    scope: channelCode,
  });

  window.location.href = '#' + Router.generate('pim_enrich_product_index', {dataLocale: localeCode});
};

const addAxisColumnsToTheProductGrid = () => {
  const storedProductGridColumns = DatagridState.get('product-grid', 'columns');
  let productGridColumns: string[] = [];
  if (storedProductGridColumns !== null) {
    productGridColumns = storedProductGridColumns.split(',');
  } else {
    //If the user has never been to the product grid since its last login
    productGridColumns = 'identifier,image,label,family,enabled,completeness,created,updated,complete_variant_products,success'.split(',');
  }
  if (!productGridColumns.includes(PRODUCT_GRID_CONSISTENCY_COLUMN)) {
    productGridColumns.push(PRODUCT_GRID_CONSISTENCY_COLUMN);
  }
  if (!productGridColumns.includes(PRODUCT_GRID_ENRICHMENT_COLUMN)) {
    productGridColumns.push(PRODUCT_GRID_ENRICHMENT_COLUMN);
  }

  return productGridColumns;
};
