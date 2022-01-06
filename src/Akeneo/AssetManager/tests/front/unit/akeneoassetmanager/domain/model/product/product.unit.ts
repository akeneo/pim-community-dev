import {Product, isProductModel} from 'akeneoassetmanager/domain/model/product/product';

const product = {
  id: '123456',
  identifier: 'nice_product',
  type: 'product',
  labels: {en_US: 'My nice product'},
  image: null,
  completeness: {completeChildren: 0, totalChildren: 0, ratio: 58},
};
const productModel: Product = {
  id: 'nice',
  identifier: 'nice_product_model',
  type: 'product_model',
  labels: {en_US: 'An awesome product model'},
  image: null,
  completeness: {completeChildren: 2, totalChildren: 4, ratio: 0},
};

describe('akeneo > asset family > domain > model --- product', () => {
  test('I can create a new product', () => {
    expect(isProductModel(productModel)).toBe(true);
    expect(isProductModel(product)).toBe(false);
  });
});
