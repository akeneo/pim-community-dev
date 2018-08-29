import reducer from 'akeneoenrichedentity/application/reducer/attribute/list';
import {denormalizeMinimalAttribute} from 'akeneoenrichedentity/domain/model/attribute/minimal';

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
          identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
          enriched_entity_identifier: 'designer',
          code: 'description',
          labels: [],
        },
        {
          type: 'text',
          identifier: {identifier: 'other', enriched_entity_identifier: 'designer'},
          enriched_entity_identifier: 'designer',
          code: 'other',
          labels: [],
        },
      ],
    };

    const deletedAttribute = {
      type: 'text',
      identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
      enriched_entity_identifier: 'designer',
      code: 'description',
      labels: [],
    };

    const newState = reducer(state, {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute});

    expect(newState).toEqual({
      attributes: [
        {
          type: 'text',
          identifier: {identifier: 'other', enriched_entity_identifier: 'designer'},
          enriched_entity_identifier: 'designer',
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
          identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
          enriched_entity_identifier: 'designer',
          code: 'description',
          labels: [],
        },
        {
          type: 'text',
          identifier: {identifier: 'description', enriched_entity_identifier: 'other_entity'},
          enriched_entity_identifier: 'other_entity',
          code: 'description',
          labels: [],
        },
      ],
    };

    const deletedAttribute = {
      type: 'text',
      identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
      enriched_entity_identifier: 'designer',
      code: 'description',
      labels: [],
      value_per_locale: false,
      value_per_channel: true,
    };

    const newState = reducer(state, {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute});

    expect(newState).toEqual({
      attributes: [
        {
          type: 'text',
          identifier: {identifier: 'description', enriched_entity_identifier: 'other_entity'},
          enriched_entity_identifier: 'other_entity',
          code: 'description',
          labels: [],
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
      identifier: {identifier: 'description', enriched_entity_identifier: 'designer'},
      enriched_entity_identifier: 'designer',
      code: 'description',
      labels: [],
    };

    const newState = reducer(state, {type: 'ATTRIBUTE_LIST_ATTRIBUTE_DELETED', deletedAttribute});

    expect(newState).toEqual({
      attributes: null,
    });
  });
});
