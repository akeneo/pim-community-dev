import reducer from 'akeneoreferenceentity/application/reducer/attribute/list';
import {denormalizeMinimalAttribute} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';

describe('akeneo > reference entity > application > reducer > attribute --- list', () => {
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

    expect(newState).toEqual({attributes: null});
  });

  test('I can receive an attribute list', () => {
    const newState = reducer(
      {attributes: []},
      {
        type: 'ATTRIBUTE_LIST_UPDATED',
        attributes: ['description', 'name'],
      }
    );

    expect(newState).toEqual({attributes: ['description', 'name']});
  });

  test('I can delete an attribute', () => {
    const state = {
      attributes: [
        {
          type: 'text',
          identifier: 'description_1234',
          reference_entity_identifier: 'designer',
          code: 'description',
          labels: {},
        },
        {
          type: 'text',
          identifier: 'name_1234',
          reference_entity_identifier: 'designer',
          code: 'name',
          labels: {},
        },
      ],
    };

    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED',
      deletedAttributeIdentifier: 'description_1234',
    });

    expect(newState).toEqual({
      attributes: [
        {
          type: 'text',
          identifier: 'name_1234',
          reference_entity_identifier: 'designer',
          code: 'name',
          labels: {},
        },
      ],
    });
  });

  test('I delete an attribute only in the right reference entity', () => {
    const state = {
      attributes: [
        {
          type: 'text',
          identifier: 'description_1234',
          reference_entity_identifier: 'designer',
          code: 'description',
          labels: {},
        },
        {
          type: 'text',
          identifier: 'name_1234',
          reference_entity_identifier: 'designer',
          code: 'name',
          labels: {},
        },
      ],
    };

    const newState = reducer(state, {
      type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED',
      deletedAttributeIdentifier: 'description_1234',
    });

    expect(newState).toEqual({
      attributes: [
        {
          type: 'text',
          identifier: 'name_1234',
          reference_entity_identifier: 'designer',
          code: 'name',
          labels: {},
        },
      ],
    });
  });

  test('I can delete an attribute without any effect if no attribute are present', () => {
    const state = {
      attributes: null,
    };

    const deletedAttribute = {
      type: 'text',
      identifier: 'description_1234',
      reference_entity_identifier: 'designer',
      code: 'description',
      labels: {},
    };

    const newState = reducer(state, {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute});

    expect(newState).toEqual({
      attributes: null,
    });
  });
});
