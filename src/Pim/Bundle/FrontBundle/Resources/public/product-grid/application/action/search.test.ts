import { productHidrator } from './search';
import Product from 'pimfront/product/domain/model/product';

describe('>>>HIDRATOR --- product', () => {
  test('hidrate raw data into products', () => {
    const data = {
      meta: {label: {en_US: 'My first label'}, image: 'asset/img.png', id: 12},
      identifier: 'my_identifier',
      family: 'my_family',
      otherData: 'not intended to be in a product'
    };

    const firstProduct = Product.clone({
      meta: {label: {en_US: 'My first label'}, image: 'asset/img.png', id: 12},
      identifier: 'my_identifier',
      family: 'my_family'
    });

    expect(productHidrator(data)).toEqual(firstProduct);
  });

  test('return empty array if no data passed', () => {
    expect(productHidrator(null)).toEqual([]);
  });
});
