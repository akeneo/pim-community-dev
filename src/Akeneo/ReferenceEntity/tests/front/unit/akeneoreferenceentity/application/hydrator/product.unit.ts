import hydrator from 'akeneoreferenceentity/application/hydrator/product';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {denormalizeProduct} from 'akeneoreferenceentity/domain/model/product/product';

describe('akeneo > reference entity > application > hydrator --- product', () => {
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
        {locale: createLocaleReference('en_US')}
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
        {locale: createLocaleReference('en_US')}
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
        {locale: createLocaleReference('en_US')}
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
    expect(() => hydrator({referenceEntityIdentifier: 'designer'})).toThrow();
  });

  test('I can hydrate a new product without identifier', () => {
    expect(
      hydrator(
        {
          id: '123456',
          document_type: 'product',
          identifier: '',
          label: 'My nice product',
          image: null,
          completeness: 60,
          variant_product_completenesses: null,
        },
        {locale: createLocaleReference('en_US')}
      )
    ).toEqual(
      denormalizeProduct({
        id: '123456',
        identifier: '',
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
  });
});
