import {ProductType} from '../domain/Product.interface';

const Router = require('pim/router');
const DatagridState = require('pim/datagrid/state');
const userContext = require('pim/user-context');

const PRODUCT_GRID_QUALITY_SCORE_COLUMN = 'data_quality_insights_score';

interface BuildFilterParams {
  channelCode: string;
  productType: ProductType;
  familyCode?: string | null;
  categoryId?: string | null;
  rootCategoryId?: string | null;
  keyIndicator?: string | null;
}

export const redirectToProductGridFilteredByFamily = (channelCode: string, localeCode: string, familyCode: string) => {
  const gridFilters = buildFilters({channelCode, productType: 'product', familyCode});
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

export const redirectToProductGridFilteredByCategory = (
  channelCode: string,
  localeCode: string,
  categoryId: string,
  rootCategoryId: string
) => {
  const gridFilters = buildFilters({channelCode, productType: 'product', categoryId, rootCategoryId});
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

export const redirectToProductGridFilteredByKeyIndicator = (
  keyIndicator: string,
  channelCode: string,
  localeCode: string,
  productType: ProductType,
  familyCode?: string | null,
  categoryId?: string | null,
  rootCategoryId?: string | null
) => {
  const gridFilters = buildFilters({channelCode, productType, familyCode, categoryId, rootCategoryId, keyIndicator});
  redirectToFilteredProductGrid(channelCode, localeCode, gridFilters);
};

const buildFilters = ({
  channelCode,
  productType,
  familyCode,
  categoryId = null,
  rootCategoryId = null,
  keyIndicator,
}: BuildFilterParams) => {
  return [
    's[updated]=1',
    `f[scope][value]=${channelCode}`,
    't=product-grid',
    productType === 'product_model' && 'f[product_typology][value]=variant',
    productType === 'product' && 'f[entity_type][value]=product',
    familyCode && `f[family][value][]=${familyCode}`,
    familyCode && 'f[family][type]=in',
    keyIndicator && `f[${keyIndicator}][value]=0`,
    `f[category][value][treeId]=${rootCategoryId}`,
    `f[category][value][categoryId]=${categoryId}`,
    'f[category][type]=1',
  ]
    .filter(Boolean)
    .join('&');
};

const redirectToFilteredProductGrid = (
  channelCode: string,
  localeCode: string,
  gridFilters: string,
  redefineColumns = true
) => {
  const productGridColumns = redefineColumns ? getProductGridColumnsWithQualityScore() : getDefaultProductGridColumns();
  DatagridState.set('product-grid', {
    columns: productGridColumns.join(','),
    filters: gridFilters,
    view: userContext.get('default_product_grid_view') ? userContext.get('default_product_grid_view') : 0,
    initialViewState: gridFilters,
    scope: channelCode,
  });

  window.location.href = '#' + Router.generate('pim_enrich_product_index', {dataLocale: localeCode});
};

const getProductGridColumnsWithQualityScore = () => {
  let productGridColumns = getDefaultProductGridColumns();
  if (!productGridColumns.includes(PRODUCT_GRID_QUALITY_SCORE_COLUMN)) {
    productGridColumns.push(PRODUCT_GRID_QUALITY_SCORE_COLUMN);
  }

  return productGridColumns;
};

const getDefaultProductGridColumns = () => {
  const storedProductGridColumns = DatagridState.get('product-grid', 'columns');
  let productGridColumns: string[] = [];
  if (storedProductGridColumns !== null) {
    productGridColumns = storedProductGridColumns.split(',');
  } else {
    //If the user has never been to the product grid since its last login
    productGridColumns =
      'identifier,image,label,family,enabled,completeness,created,updated,complete_variant_products,success'.split(',');
  }

  return productGridColumns;
};
