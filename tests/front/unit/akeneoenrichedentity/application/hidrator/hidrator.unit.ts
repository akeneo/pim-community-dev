import hidrateAll from 'akeneoenrichedentity/application/hidrator/hidrator';

describe('akeneo > enriched entity > application > hidrator --- hidrator', () => {
  test('I can hidrate a collection of elements', () => {
    const hidrator = element => {
      expect(element).toEqual('element_to_hidrate');

      return 'hidrated_element';
    };
    const hidratedElements = hidrateAll(hidrator)({element: 'element_to_hidrate'});

    expect(hidratedElements).toEqual(['hidrated_element']);
  });

  test('I can hidrate an array of elements', () => {
    const hidrator = element => {
      expect(element).toEqual('element_to_hidrate');

      return 'hidrated_element';
    };
    const hidratedElements = hidrateAll(hidrator)(['element_to_hidrate']);

    expect(hidratedElements).toEqual(['hidrated_element']);
  });

  test('I can hidrate an empty collection', () => {
    const hidrator = element => {
      expect(element).toEqual('element_to_hidrate');

      return 'hidrated_element';
    };
    const hidratedElements = hidrateAll(hidrator)(null);

    expect(hidratedElements).toEqual([]);
  });
});
