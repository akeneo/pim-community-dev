import reducer from 'akeneoreferenceentity/application/reducer/record/edit/products';

describe('akeneo > reference entity > application > reducer > record > edit --- product', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can generate a default state', () => {
    const newState = reducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual({
      selectedAttribute: null,
      attributes: [],
      products: [],
    });
  });

  test('I can update the list of attributes', () => {
    const state = {
      selectedAttribute: null,
      attributes: [],
      products: [],
    };
    const newState = reducer(state, {
      type: 'PRODUCT_LIST_ATTRIBUTE_LIST_UPDATED',
      attributes: [
        {
          code: 'front_view',
          type: 'akeneo_reference_entity',
          labels: {en_US: 'Nice front view'},
          reference_data_name: 'brand',
        },
      ],
    });

    expect(newState).toEqual({
      selectedAttribute: null,
      attributes: [
        {
          code: 'front_view',
          type: 'akeneo_reference_entity',
          labels: {en_US: 'Nice front view'},
          reference_data_name: 'brand',
        },
      ],
      products: [],
    });
  });

  test('I can update the list of products', () => {
    const state = {
      selectedAttribute: null,
      attributes: [],
      products: [],
    };
    const newState = reducer(state, {
      type: 'PRODUCT_LIST_PRODUCT_LIST_UPDATED',
      products: [
        {
          id: '123456',
          identifier: 'nice_product',
          type: 'product',
          labels: {en_US: 'My nice product'},
          image: null,
        },
      ],
    });

    expect(newState).toEqual({
      selectedAttribute: null,
      attributes: [],
      products: [
        {
          id: '123456',
          identifier: 'nice_product',
          type: 'product',
          labels: {en_US: 'My nice product'},
          image: null,
        },
      ],
    });
  });

  test('I can select a new attribute', () => {
    const state = {
      selectedAttribute: null,
      attributes: [
        {
          code: 'front_view',
          type: 'akeneo_reference_entity',
          labels: {en_US: 'Nice front view'},
          reference_data_name: 'brand',
        },
      ],
      products: [],
    };
    const newState = reducer(state, {
      type: 'PRODUCT_LIST_ATTRIBUTE_SELECTED',
      attributeCode: 'front_view',
    });

    expect(newState).toEqual({
      selectedAttribute: 'front_view',
      attributes: [
        {
          code: 'front_view',
          type: 'akeneo_reference_entity',
          labels: {en_US: 'Nice front view'},
          reference_data_name: 'brand',
        },
      ],
      products: [],
    });
  });
});
