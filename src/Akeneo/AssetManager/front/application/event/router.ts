import {NormalizedCode as NormalizedAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {NormalizedCode as NormalizedAttributeCode} from 'akeneoassetmanager/domain/model/product/attribute/code';

export const redirectToRoute = (
  route: string,
  params: any = {},
  options: {trigger: boolean; replace: boolean} = {trigger: false, replace: false}
) => {
  return {type: 'REDIRECT_TO_ROUTE', route, params, options};
};

export const redirectToProductGrid = (selectedAttribute: NormalizedAttributeCode, assetCode: NormalizedAssetCode) => {
  return {type: 'REDIRECT_TO_PRODUCT_GRID', selectedAttribute, assetCode};
};
