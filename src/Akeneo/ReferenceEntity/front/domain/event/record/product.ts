import {NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';

export const productListAttributeListUpdated = (attributes: any[]) => {
  return {type: 'PRODUCT_LIST_ATTRIBUTE_LIST_UPDATED', attributes};
};

export const productListAttributeSelected = (attributeCode: string) => {
  return {type: 'PRODUCT_LIST_ATTRIBUTE_SELECTED', attributeCode};
};

export const productListProductListUpdated = (products: NormalizedProduct[]) => {
  return {type: 'PRODUCT_LIST_PRODUCT_LIST_UPDATED', products};
};
