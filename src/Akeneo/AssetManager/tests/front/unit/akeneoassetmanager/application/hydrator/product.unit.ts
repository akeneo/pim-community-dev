import hydrator from 'akeneoassetmanager/application/hydrator/product';
import {denormalizeProduct} from 'akeneoassetmanager/domain/model/product/product';

describe('akeneo > asset family > application > hydrator --- product', () => {
  test('I can hydrate a new product', () => {
    expect(
      hydrator(
        {
          identifier: 'nice_product',
          id: '123456',
          document_type: 'product',
          label: 'My nice product',
          image: null,
          completeness: 60,
          variant_product_completenesses: null,
        },
        {locale: 'en_US'}
      )
    ).toEqual(
      denormalizeProduct({
        id: '123456',
        identifier: 'nice_product',
        type: 'product',
        labels: {en_US: 'My nice product'},
        image: null,
        completeness: {
          completeChildren: 0,
          totalChildren: 0,
          ratio: 60,
        },
      })
    );
    expect(
      hydrator(
        {
          identifier: 'nice_product',
          id: '123456',
          document_type: 'product_model',
          label: 'My nice product',
          image: null,
          completeness: null,
          variant_product_completenesses: {completeChildren: 2, totalChildren: 4},
        },
        {locale: 'en_US'}
      )
    ).toEqual(
      denormalizeProduct({
        id: '123456',
        identifier: 'nice_product',
        type: 'product_model',
        labels: {en_US: 'My nice product'},
        image: null,
        completeness: {
          completeChildren: 2,
          totalChildren: 4,
          ratio: 0,
        },
      })
    );
    expect(
      hydrator(
        {
          identifier: 'nice_product',
          id: '123456',
          document_type: 'product_model',
          label: 'My nice product',
          image: null,
        },
        {locale: 'en_US'}
      )
    ).toEqual(
      denormalizeProduct({
        id: '123456',
        identifier: 'nice_product',
        type: 'product_model',
        labels: {en_US: 'My nice product'},
        image: null,
        completeness: {
          completeChildren: 0,
          totalChildren: 0,
          ratio: 0,
        },
      })
    );
  });

  test('It throw an error if I pass a malformed product', () => {
    expect(() => hydrator({})).toThrow();
    expect(() => hydrator({labels: {}})).toThrow();
    expect(() => hydrator({identifier: 'starck'})).toThrow();
    expect(() => hydrator({assetFamilyIdentifier: 'designer'})).toThrow();
  });
});
