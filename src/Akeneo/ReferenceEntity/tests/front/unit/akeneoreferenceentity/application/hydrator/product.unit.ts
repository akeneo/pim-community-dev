import hydrator from 'akeneoreferenceentity/application/hydrator/product';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';

describe('akeneo > reference entity > application > hydrator --- product', () => {
  test('I can hydrate a new product', () => {
    expect(
      hydrator({
        identifier: 'nice_product',
        meta: {
          id: '123456',
          identifier: 'nice_product',
          model_type: 'product',
          label: {en_US: 'My nice product'},
          image: null,
          completenesses: [],
        },
      })
    );
    expect(
      hydrator(
        {
          identifier: 'nice_product',
          meta: {
            id: '123456',
            identifier: 'nice_product',
            model_type: 'product',
            label: {en_US: 'My nice product'},
            image: null,
            completenesses: [
              {
                channel: 'ecommerce',
                locales: {
                  en_US: {
                    completeness: {
                      required: 10,
                      missing: 2,
                    },
                  },
                },
              },
            ],
          },
        },
        {locale: createLocaleReference('en_US'), channel: createChannelReference('ecommerce')}
      )
    );
    expect(
      hydrator(
        {
          code: 'nice_product',
          meta: {
            id: '123456',
            code: 'nice_product',
            model_type: 'product_model',
            label: {en_US: 'My nice product'},
            image: null,
            variant_product_completenesses: {
              completenesses: {
                ecommerce: {
                  en_US: 1,
                },
              },
              total: 2,
            },
          },
        },
        {locale: createLocaleReference('en_US'), channel: createChannelReference('ecommerce')}
      )
    );
  });

  test('It throw an error if I pass a malformed product', () => {
    expect(() => hydrator({})).toThrow();
    expect(() => hydrator({labels: {}})).toThrow();
    expect(() => hydrator({identifier: 'starck'})).toThrow();
    expect(() => hydrator({referenceEntityIdentifier: 'designer'})).toThrow();
  });
});
