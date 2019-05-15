import hydrator from 'akeneoreferenceentity/application/hydrator/product';

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
});
