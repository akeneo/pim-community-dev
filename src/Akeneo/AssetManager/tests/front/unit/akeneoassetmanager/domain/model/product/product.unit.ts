import {productidentifiersAreEqual} from 'akeneoassetmanager/domain/model/product/identifier';
import {createProduct, denormalizeProduct, isProductModel} from 'akeneoassetmanager/domain/model/product/product';
import {createFileFromNormalized} from 'akeneoassetmanager/domain/model/file';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';

const product = denormalizeProduct({
  id: '123456',
  identifier: 'nice_product',
  type: 'product',
  labels: {en_US: 'My nice product'},
  image: null,
  completeness: {completeChildren: 0, totalChildren: 0, ratio: 58},
});
const productModel = denormalizeProduct({
  id: 'nice',
  identifier: 'nice_product_model',
  type: 'product_model',
  labels: {en_US: 'An awesome product model'},
  image: null,
  completeness: {completeChildren: 2, totalChildren: 4, ratio: 0},
});

describe('akeneo > asset family > domain > model --- product', () => {
  test('I can create a new product', () => {
    expect(productidentifiersAreEqual(product.getIdentifier(), 'nice_product')).toBe(true);
  });

  test('I can compare two products', () => {
    expect(product.equals(productModel)).toEqual(false);
    expect(product.equals(product)).toEqual(true);
  });

  test('I can tell if an entity is a Product model', () => {
    expect(isProductModel(product)).toBe(false);
    expect(isProductModel(productModel)).toBe(true);
  });

  test('I can get the id of a product', () => {
    expect(product.getId()).toEqual('123456');
  });

  test('I can get the type of a product', () => {
    expect(product.getType()).toEqual('product');
    expect(productModel.getType()).toEqual('product_model');
  });

  test('I can get the label of a product', () => {
    expect(product.getLabel('en_US')).toEqual('My nice product');
    expect(product.getLabel('fr_FR')).toEqual('[nice_product]');
    expect(product.getLabel('fr_FR', false)).toEqual('');
  });

  test('I can get the image of a product', () => {
    expect(product.getImage()).toEqual(createEmptyFile());
  });

  test('I can get the label collection of a product', () => {
    expect(product.getLabelCollection()).toEqual({en_US: 'My nice product'});
  });

  test('I can normalize my product', () => {
    expect(product.normalize()).toEqual({
      id: '123456',
      identifier: 'nice_product',
      type: 'product',
      labels: {en_US: 'My nice product'},
      image: null,
      completeness: {completeChildren: 0, totalChildren: 0, ratio: 58},
    });
  });

  test('I cannot create a malformed product', () => {
    expect(() => {
      createProduct('123456', 'nice_product', 'nice', {en_US: 'My nice product'}, createFileFromNormalized(null));
    }).toThrow('Product expects an ProductType as type argument');

    expect(() => {
      createProduct('123456', 'nice_product', 'product', {en_US: 'My nice product'}, createFileFromNormalized(null));
    }).toThrow('Product expects a Completeness as completeness argument');
  });
});
