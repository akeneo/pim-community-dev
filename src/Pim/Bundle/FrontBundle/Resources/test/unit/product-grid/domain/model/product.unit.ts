import {Product, ModelType} from 'pimfront/product-grid/domain/model/product';

describe('>>>MODEL --- product', () => {
  test('get label with existing locale', () => {
    const product = Product.create({
      meta: {
        label: {en_US: 'My label'},
        image: {filePath: 'asset/img.png', originalFilename: 'toto.png'},
        id: 12,
        completenesses: {},
        model_type: ModelType.Product,
        has_children: false,
      },
      identifier: 'my_identifier',
      family: 'my_family',
    });

    expect(product.getLabel('ecommerce', 'en_US')).toBe('My label');
  });

  test('fallback to identifier when translation is not available', () => {
    const product = Product.create({
      meta: {
        label: {en_US: 'My label'},
        id: 12,
        image: {filePath: 'asset/img.png', originalFilename: 'toto.png'},
        completenesses: {},
        model_type: ModelType.Product,
        has_children: false,
      },
      identifier: 'my_identifier',
      family: 'my_family',
    });

    expect(product.getLabel('ecommerce', 'fr_FR')).toBe('my_identifier');
  });
});
