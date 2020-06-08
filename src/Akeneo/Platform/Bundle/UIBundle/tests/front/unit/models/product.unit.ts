import {
  getProductsType,
  getProductType,
  ProductType,
  ProductsType,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/product';

describe('product', () => {
  it('should return the corresponding productsType for the provided productType', () => {
    expect(getProductsType(ProductType.Product)).toEqual(ProductsType.Products);
    expect(getProductsType(ProductType.ProductModel)).toEqual(ProductsType.ProductModels);
  });

  it('should return the corresponding productType for the provided productsType', () => {
    expect(getProductType(ProductsType.Products)).toEqual(ProductType.Product);
    expect(getProductType(ProductsType.ProductModels)).toEqual(ProductType.ProductModel);
  });
});
