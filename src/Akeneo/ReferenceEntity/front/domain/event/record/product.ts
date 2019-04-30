import {NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';
import AttributeCode from 'akeneoreferenceentity/domain/model/product/attribute/code';
import Attribute from 'akeneoreferenceentity/domain/model/product/attribute';

export const productListAttributeListUpdated = (attributes: Attribute[]) => {
  return {
    type: 'PRODUCT_LIST_ATTRIBUTE_LIST_UPDATED',
    attributes: attributes.map((attribute: Attribute) => attribute.normalize()),
  };
};

export const productListAttributeSelected = (attributeCode: AttributeCode) => {
  return {type: 'PRODUCT_LIST_ATTRIBUTE_SELECTED', attributeCode};
};

export const productListProductListUpdated = (products: NormalizedProduct[]) => {
  return {type: 'PRODUCT_LIST_PRODUCT_LIST_UPDATED', products};
};
