import {
  getProductsType,
  getProductType,
} from '../../../../Resources/public/js/product/form/quantified-associations/models';

describe('product', () => {
  it('should return the corresponding productsType for the provided productType', () => {
    expect(getProductsType('product')).toEqual('products');
    expect(getProductsType('product_model')).toEqual('product_models');
  });

  it('should return the corresponding productType for the provided productsType', () => {
    expect(getProductType('products')).toEqual('product');
    expect(getProductType('product_models')).toEqual('product_model');
  });
});
