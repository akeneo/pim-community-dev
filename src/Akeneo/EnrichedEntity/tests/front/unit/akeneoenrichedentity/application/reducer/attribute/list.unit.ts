import reducer from 'akeneoenrichedentity/application/reducer/attribute/list';
import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';

describe('akeneo > enriched entity > application > reducer > attribute --- list', () => {
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

    expect(newState).toEqual({attributes: [], openedAttribute: null});
  });

  test('I can receive an attribute list', () => {
    const newState = reducer(
      {attributes: [], openedAttribute: null},
      {
        type: 'ATTRIBUTE_LIST_UPDATED',
        attributes: ['description', 'name'],
      }
    );

    expect(newState).toEqual({attributes: ['description', 'name'], openedAttribute: null});
  });

  test('I can delete an attribute', () => {
    const state = {
      attributes: [
        {
          type: 'text',
          identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
          enrichedEntityIdentifier: 'designer',
          code: 'description',
          labels: [],
        },
        {
          type: 'text',
          identifier: {identifier: 'other', enrichedEntityIdentifier: 'designer'},
          enrichedEntityIdentifier: 'designer',
          code: 'other',
          labels: [],
        },
      ],
    };

    const deletedAttribute = denormalizeAttribute({
      type: 'text',
      identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
      enrichedEntityIdentifier: 'designer',
      code: 'description',
      labels: [],
    });

    const newState = reducer(state, {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute: deletedAttribute});

    expect(newState).toEqual({
      attributes: [
        {
          type: 'text',
          identifier: {identifier: 'other', enrichedEntityIdentifier: 'designer'},
          enrichedEntityIdentifier: 'designer',
          code: 'other',
          labels: [],
        },
      ],
    });
  });

  test('I delete an attribute only in the right enriched entity', () => {
    const state = {
      attributes: [
        {
          type: 'text',
          identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
          enrichedEntityIdentifier: 'designer',
          code: 'description',
          labels: [],
        },
        {
          type: 'text',
          identifier: {identifier: 'description', enrichedEntityIdentifier: 'other_entity'},
          enrichedEntityIdentifier: 'other_entity',
          code: 'description',
          labels: [],
        },
      ],
    };

    const deletedAttribute = denormalizeAttribute({
      type: 'text',
      identifier: {identifier: 'description', enrichedEntityIdentifier: 'designer'},
      enrichedEntityIdentifier: 'designer',
      code: 'description',
      labels: [],
    });

    const newState = reducer(state, {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute: deletedAttribute});

    expect(newState).toEqual({
      attributes: [
        {
          type: 'text',
          identifier: {identifier: 'description', enrichedEntityIdentifier: 'other_entity'},
          enrichedEntityIdentifier: 'other_entity',
          code: 'description',
          labels: [],
        },
      ],
    });
  });
});
