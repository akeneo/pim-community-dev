import hydrateAll, {validateKeys, InvalidRawObjectError} from 'akeneoreferenceentity/application/hydrator/hydrator';

describe('akeneo > reference entity > application > hydrator --- hydrator', () => {
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

  test('I can validate the presence of keys in an object', () => {
    expect(() => {
      validateKeys({name: 'didier', age: 20}, ['name', 'height', 'parents'], '');
    }).toThrow();
    expect(validateKeys({name: 'didier', age: 20, height: 160}, ['name', 'age'], '')).toBeUndefined();
  });

  test('I can throw InvalidRawObjectError', () => {
    expect(() => {
      throw new InvalidRawObjectError(
        'The provided raw reference entity seems to be malformed.',
        ['name'],
        ['height'],
        {
          age: 12,
        }
      );
    }).toThrow();
  });
});
