import hidrator from './product';
import Product from 'pimfront/js/product/domain/model/product';

test('hidrate raw data into products', () => {
  const data = [
    {
      meta: {label: {en_US: 'My first label'}, image: 'asset/img.png', id: 12},
      identifier: 'my_identifier',
      family: 'my_family',
      otherData: 'not intended to be in a product'
    },
    {
      meta: {label: {en_US: 'My second label'}, image: 'asset/img.png', id: 13},
      identifier: 'my_identifier',
      family: 'my_family',
      otherData: 'not intended to be in a product'
    }
  ];

  const firstProduct = Product.clone({
    meta: {label: {en_US: 'My first label'}, image: 'asset/img.png', id: 12},
    identifier: 'my_identifier',
    family: 'my_family'
  });

  const secondProduct = Product.clone({
    meta: {label: {en_US: 'My second label'}, image: 'asset/img.png', id: 13},
    identifier: 'my_identifier',
    family: 'my_family'
  });

  expect(hidrator(data)).toEqual([firstProduct, secondProduct]);
});
