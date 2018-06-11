import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';

describe('akeneo > enriched entity > application > hydrator --- hydrator', () => {
  test('I can hydrate a collection of elements', () => {
    const hydrator = element => {
      expect(element).toEqual('element_to_hydrate');

      return 'hydrated_element';
    };
    const hydratedElements = hydrateAll(hydrator)({element: 'element_to_hydrate'});

    expect(hydratedElements).toEqual(['hydrated_element']);
  });

  test('I can hydrate an array of elements', () => {
    const hydrator = element => {
      expect(element).toEqual('element_to_hydrate');

      return 'hydrated_element';
    };
    const hydratedElements = hydrateAll(hydrator)(['element_to_hydrate']);

    expect(hydratedElements).toEqual(['hydrated_element']);
  });

  test('I can hydrate an empty collection', () => {
    const hydrator = element => {
      expect(element).toEqual('element_to_hydrate');

      return 'hydrated_element';
    };
    const hydratedElements = hydrateAll(hydrator)(null);

    expect(hydratedElements).toEqual([]);
  });
});
