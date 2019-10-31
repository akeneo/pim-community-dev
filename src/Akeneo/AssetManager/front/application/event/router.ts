import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

export const redirectToRoute = (
  route: string,
  params: any = {},
  options: {trigger: boolean; replace: boolean} = {trigger: false, replace: false}
) => {
  return {type: 'REDIRECT_TO_ROUTE', route, params, options};
};

export const redirectToProductGrid = (selectedAttribute: AttributeCode, assetCode: AssetCode) => {
  return {type: 'REDIRECT_TO_PRODUCT_GRID', selectedAttribute, assetCode};
};
