import {
  productReducer,
  valuesUpdated,
  selectCurrentValues,
  valueChanged,
  updateValueData,
  labelsUpdated,
  selectProductLabels,
  productIdentifierChanged,
  selectProductIdentifer,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';

test('It ignores other commands', () => {
  const state = {};
  const newState = productReducer(state, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toMatchObject(state);
});

test('It should generate a default state', () => {
  const newState = productReducer(undefined, {
    type: 'ANOTHER_ACTION',
  });

  expect(newState).toEqual({identifier: null, values: [], labels: {}});
});

test('It should update the identifier in the state', () => {
  const state = {identifier: null, values: [], labels: {}};
  const identifier = '594877';
  const newState = productReducer(state, {
    type: 'PRODUCT_IDENTIFIER_CHANGED',
    payload: identifier,
  });

  expect(newState).toEqual({identifier, values: [], labels: {}});
});

test('It should update the value collection in the state', () => {
  const state = {identifier: null, values: [], labels: {}};
  const values = [
    {
      attribute: {
        code: 'smartphone-apple',
        labels: {en_US: 'Smartphone Apple'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-apple',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone-7.jpg', 'iphone-8.jpg'],
      editable: true,
    },
    {
      attribute: {
        code: 'smartphone-honor',
        labels: {en_US: 'Smartphone Honor'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-honor',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
      editable: true,
    },
  ];
  const newState = productReducer(state, {
    type: 'VALUE_COLLECTION_UPDATED',
    values,
  });

  expect(newState).toEqual({identifier: null, values, labels: {}});
});

test('It should update the label collection in the state', () => {
  const state = {identifier: null, values: [], labels: {}};
  const labels = {en_US: 'So nice product'};
  const newState = productReducer(state, {
    type: 'LABEL_COLLECTION_UPDATED',
    labels,
  });

  expect(newState).toEqual({identifier: null, values: [], labels});
});

test('It should have an action to update the identifier', () => {
  const identifier = '594877';
  const expectedAction = {
    type: 'PRODUCT_IDENTIFIER_CHANGED',
    payload: identifier,
  };

  expect(productIdentifierChanged(identifier)).toMatchObject(expectedAction);
});

test('It should have an action to update the values', () => {
  const values = [
    {
      attribute: {
        code: 'smartphone-apple',
        labels: {en_US: 'Smartphone Apple'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-apple',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone-7.jpg', 'iphone-8.jpg'],
      editable: true,
    },
    {
      attribute: {
        code: 'smartphone-honor',
        labels: {en_US: 'Smartphone Honor'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-honor',
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
      editable: true,
    },
  ];
  const expectedAction = {
    type: 'VALUE_COLLECTION_UPDATED',
    values,
  };

  expect(valuesUpdated(values)).toMatchObject(expectedAction);
});

test('It should have an action to update the labels', () => {
  const labels = {en_US: 'So nice product'};
  const expectedAction = {
    type: 'LABEL_COLLECTION_UPDATED',
    labels,
  };

  expect(labelsUpdated(labels)).toMatchObject(expectedAction);
});

test('It should be able to select the current identifier', () => {
  const identifier = '594877';

  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null},
    product: {identifier, values: [], labels: {}},
  };

  expect(selectProductIdentifer(state)).toEqual(identifier);
});

test('It should be able to select the current values', () => {
  const values = [
    {
      attribute: {
        code: 'smartphone-apple',
        labels: {en_US: 'Smartphone Apple'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-apple',
        availableLocales: [],
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone-7.jpg', 'iphone-8.jpg'],
      editable: true,
    },
    {
      attribute: {
        code: 'smartphone-honor',
        labels: {en_US: 'Smartphone Honor'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-honor',
        availableLocales: [],
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
      editable: true,
    },
  ];

  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null},
    product: {identifier: null, values, labels: {}},
  };

  expect(selectCurrentValues(state)).toEqual(values);
});

test('It should filter the current values for locale specific attributes', () => {
  const values = [
    {
      attribute: {
        code: 'smartphone-apple',
        labels: {en_US: 'Smartphone Apple'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-apple',
        availableLocales: ['en_US'],
      },
      locale: 'en_US',
      channel: 'ecommerce',
      data: ['iphone-7.jpg', 'iphone-8.jpg'],
      editable: true,
    },
    {
      attribute: {
        code: 'smartphone-honor',
        labels: {en_US: 'Smartphone Honor'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-honor',
        availableLocales: ['fr_FR'],
      },
      locale: null,
      channel: 'ecommerce',
      data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
      editable: true,
    },
    {
      attribute: {
        code: 'smartphone-samsung',
        labels: {en_US: 'Smartphone Samsung'},
        group: 'marketing',
        isReadOnly: false,
        referenceDataName: 'smartphone-samsung',
        availableLocales: ['en_US'],
      },
      locale: null,
      channel: 'ecommerce',
      data: ['galaxy-s10.jpg'],
      editable: true,
    },
  ];

  const expectedValues = [values[0], values[2]];

  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null},
    product: {identifier: null, values, labels: {}},
  };

  expect(selectCurrentValues(state)).toEqual(expectedValues);
});

test('It should be able to select the current labels', () => {
  const labels = {en_US: 'So nice product'};

  const state = {
    context: {channel: 'ecommerce', locale: 'en_US'},
    structure: {attributes: [], channels: [], family: null},
    product: {identifier: null, values: [], labels},
  };

  expect(selectProductLabels(state)).toEqual(labels);
});

test('It should be able to edit a value in the collection', () => {
  const state = {
    identifier: null,
    values: [
      {
        attribute: {
          code: 'smartphone-apple',
          labels: {en_US: 'Smartphone Apple'},
          group: 'marketing',
          isReadOnly: false,
          referenceDataName: 'smartphone-apple',
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['iphone-7.jpg', 'iphone-8.jpg'],
        editable: true,
      },
      {
        attribute: {
          code: 'smartphone-honor',
          labels: {en_US: 'Smartphone Honor'},
          group: 'marketing',
          isReadOnly: false,
          referenceDataName: 'smartphone-honor',
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
        editable: true,
      },
    ],
    labels: {},
  };

  const valueToUpdate = {
    attribute: {
      code: 'smartphone-honor',
      labels: {en_US: 'Smartphone Honor'},
      group: 'marketing',
      isReadOnly: false,
      referenceDataName: 'smartphone-honor',
    },
    locale: 'en_US',
    channel: 'ecommerce',
    data: ['honor-7x.jpg'],
    editable: true,
  };

  const newState = productReducer(state, {
    type: 'VALUE_CHANGED',
    value: valueToUpdate,
  });

  expect(newState.values).toEqual([state.values[0], valueToUpdate]);
});

test('It should be able to generate a productIdentifierChanged action', () => {
  expect(productIdentifierChanged('594877')).toEqual({type: 'PRODUCT_IDENTIFIER_CHANGED', payload: '594877'});
});

test('It should be able to generate productIdentifierChanged action', () => {
  expect(valueChanged('my_value')).toEqual({type: 'VALUE_CHANGED', value: 'my_value'});
});

test('It should be able to update the data of a value', () => {
  expect(updateValueData({data: [], locale: 'en_US'}, ['nice_asset'])).toEqual({locale: 'en_US', data: ['nice_asset']});
});
