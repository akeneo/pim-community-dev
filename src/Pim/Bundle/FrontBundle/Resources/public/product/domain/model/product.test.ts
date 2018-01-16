import Product from './product';

describe('>>>MODEL --- product', () => {
  test('get label with existing locale', () => {
    const product = Product.clone({
      meta: {
        label: {en_US: 'My label'},
        image: {filePath: 'asset/img.png', originalFilename: 'toto.png'},
        id: 12,
        completenesses: {}
      },
      identifier: 'my_identifier',
      family: 'my_family'
    });

    expect(product.getLabel('ecommerce', 'en_US')).toBe('My label');
  });

  test('fallback to identifier when translation is not available', () => {
    const product = Product.clone({
      meta: {
        label: {en_US: 'My label'},
        id: 12,
        image: {filePath: 'asset/img.png', originalFilename: 'toto.png'},
        completenesses: {}
      },
      identifier: 'my_identifier',
      family: 'my_family'
    });

    expect(product.getLabel('ecommerce', 'fr_FR')).toBe('my_identifier');
  });
});
