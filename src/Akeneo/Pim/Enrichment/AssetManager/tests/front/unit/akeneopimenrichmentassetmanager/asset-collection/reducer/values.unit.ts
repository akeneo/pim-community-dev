import {valuesReducer, valuesUpdated, selectCurrentValues} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';

describe('akeneo > enrichment > asset collection > reducer > values', () => {
  test('It ignore other commands', () => {
    const state = {};
    const newState = valuesReducer(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toMatchObject(state);
  });

  test('It should generate a default state', () => {
    const newState = valuesReducer(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual([]);
  });

  test('It should update the value collection in the state', () => {
    const state = {};
    const values = [
      {
        attribute : {
          code: 'smartphone-apple', 
          labels: {'en_US': 'Smartphone Apple'}, 
          group: 'marketing', 
          isReadOnly: false, 
          referenceDataName: 'smartphone-apple'
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['iphone-7.jpg', 'iphone-8.jpg'],
        editable: true
      },
      {
        attribute : {
          code: 'smartphone-honor', 
          labels: {'en_US': 'Smartphone Honor'}, 
          group: 'marketing', 
          isReadOnly: false, 
          referenceDataName: 'smartphone-honor'
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
        editable: true
      }
    ]
    const newState = valuesReducer(state, {
      type: 'VALUE_COLLECTION_UPDATED',
      values
    });

    expect(newState).toEqual(values);
  });

  test('It should have an action to update the values', () => {
    const values = [
      {
        attribute : {
          code: 'smartphone-apple', 
          labels: {'en_US': 'Smartphone Apple'}, 
          group: 'marketing', 
          isReadOnly: false, 
          referenceDataName: 'smartphone-apple'
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['iphone-7.jpg', 'iphone-8.jpg'],
        editable: true
      },
      {
        attribute : {
          code: 'smartphone-honor', 
          labels: {'en_US': 'Smartphone Honor'}, 
          group: 'marketing', 
          isReadOnly: false, 
          referenceDataName: 'smartphone-honor'
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
        editable: true
      }
    ]
    const expectedAction = {
      type: 'VALUE_COLLECTION_UPDATED',
      values
    };

    expect(valuesUpdated(values)).toMatchObject(expectedAction);
  });

  test('It should be able to select the current values', () => {
    const values = [
      {
        attribute : {
          code: 'smartphone-apple', 
          labels: {'en_US': 'Smartphone Apple'}, 
          group: 'marketing', 
          isReadOnly: false, 
          referenceDataName: 'smartphone-apple'
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['iphone-7.jpg', 'iphone-8.jpg'],
        editable: true
      },
      {
        attribute : {
          code: 'smartphone-honor', 
          labels: {'en_US': 'Smartphone Honor'}, 
          group: 'marketing', 
          isReadOnly: false, 
          referenceDataName: 'smartphone-honor'
        },
        locale: 'en_US',
        channel: 'ecommerce',
        data: ['honor-10-lite.jpg', 'honor-7x.jpg'],
        editable: true
      }
    ]
    const state = {
      context: {channel: 'ecommerce', locale: 'en_US'},
      structure: {attributes: [], channels: [], family: null},
      values: values
    };

    expect(selectCurrentValues(state)).toEqual(values);
  });
});