import Product from 'akeneoassetmanager/domain/model/product/product';
import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import Attribute from 'akeneoassetmanager/domain/model/product/attribute';

export const productListAttributeListUpdated = (attributes: Attribute[]) => {
  return {
    type: 'PRODUCT_LIST_ATTRIBUTE_LIST_UPDATED',
    attributes: attributes.map((attribute: Attribute) => attribute.normalize()),
  };
};

export const productListAttributeSelected = (attributeCode: AttributeCode) => {
  return {type: 'PRODUCT_LIST_ATTRIBUTE_SELECTED', attributeCode};
};

export const productListProductListUpdated = (products: Product[], totalCount: number) => {
  return {
    type: 'PRODUCT_LIST_PRODUCT_LIST_UPDATED',
    products: products.map((product: Product) => product.normalize()),
    totalCount,
  };
};
