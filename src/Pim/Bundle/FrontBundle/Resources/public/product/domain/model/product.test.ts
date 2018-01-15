import Product from './product';

test('get label with existing locale', () => {
  const product = Product.clone({
    meta: {label: {en_US: 'My label'}, image: 'asset/img.png', id: 12},
    identifier: 'my_identifier',
    family: 'my_family'
  });

  expect(product.getLabel('ecommerce', 'en_US')).toBe('My label');
});

test('fallback to identifier when translation is not available', () => {
  const product = Product.clone({
    meta: {label: {en_US: 'My label'}, image: 'asset/img.png', id: 12},
    identifier: 'my_identifier',
    family: 'my_family'
  });

  expect(product.getLabel('ecommerce', 'fr_FR')).toBe('my_identifier');
});
