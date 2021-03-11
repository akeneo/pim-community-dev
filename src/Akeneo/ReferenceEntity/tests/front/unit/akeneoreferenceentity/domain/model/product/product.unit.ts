import {createIdentifier as denormalizeProductIdentifier} from 'akeneoreferenceentity/domain/model/product/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createProduct, denormalizeProduct, isProductModel} from 'akeneoreferenceentity/domain/model/product/product';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';

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

describe('akeneo > reference entity > domain > model --- product', () => {
  test('I can create a new product', () => {
    expect(product.getIdentifier()).toEqual(denormalizeProductIdentifier('nice_product'));
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
    expect(product.getId().stringValue()).toEqual('123456');
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
    expect(product.getLabelCollection()).toEqual(createLabelCollection({en_US: 'My nice product'}));
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
      createProduct(
        '123456',
        denormalizeProductIdentifier('nice_product'),
        'product',
        createLabelCollection({en_US: 'My nice product'}),
        denormalizeFile(null)
      );
    }).toThrow('Product expects an ProductIdentifier as id argument');

    expect(() => {
      createProduct(
        denormalizeProductIdentifier('123456'),
        'nice_product',
        'product',
        createLabelCollection({en_US: 'My nice product'}),
        denormalizeFile(null)
      );
    }).toThrow('Product expects an ProductIdentifier as identifier argument');

    expect(() => {
      createProduct(
        denormalizeProductIdentifier('123456'),
        denormalizeProductIdentifier('nice_product'),
        'nice',
        createLabelCollection({en_US: 'My nice product'}),
        denormalizeFile(null)
      );
    }).toThrow('Product expects an ProductType as type argument');

    expect(() => {
      createProduct(
        denormalizeProductIdentifier('123456'),
        denormalizeProductIdentifier('nice_product'),
        'product',
        {en_US: 'My nice product'},
        denormalizeFile(null)
      );
    }).toThrow('Product expects a LabelCollection as labelCollection argument');

    expect(() => {
      createProduct(
        denormalizeProductIdentifier('123456'),
        denormalizeProductIdentifier('nice_product'),
        'product',
        createLabelCollection({en_US: 'My nice product'}),
        null
      );
    }).toThrow('Product expects a File as image argument');

    expect(() => {
      createProduct(
        denormalizeProductIdentifier('123456'),
        denormalizeProductIdentifier('nice_product'),
        'product',
        createLabelCollection({en_US: 'My nice product'}),
        denormalizeFile(null)
      );
    }).toThrow('Product expects a Completeness as completeness argument');
  });
});
