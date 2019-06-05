import {NormalizedCode as NormalizedRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import {NormalizedCode as NormalizedAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';

export const redirectToRoute = (
  route: string,
  params: any = {},
  options: {trigger: boolean; replace: boolean} = {trigger: false, replace: false}
) => {
  return {type: 'REDIRECT_TO_ROUTE', route, params, options};
};

export const redirectToProductGrid = (selectedAttribute: NormalizedAttributeCode, recordCode: NormalizedRecordCode) => {
  return {type: 'REDIRECT_TO_PRODUCT_GRID', selectedAttribute, recordCode};
};
