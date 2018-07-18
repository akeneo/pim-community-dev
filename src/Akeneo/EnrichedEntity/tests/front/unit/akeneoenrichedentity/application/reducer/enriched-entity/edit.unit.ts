import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import reducer from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';

describe('akeneo > enriched entity > application > reducer > enriched-entity --- edit', () => {
  test('I ignore other commands', () => {
    const state = {};
    const newState = reducer.enrichedEntity(state, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(state);
  });

  test('I can generate a default state', () => {
    const newState = reducer.enrichedEntity(undefined, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toEqual(null);
  });

  test('I can add the enriched entity', () => {
    const state = {};
    const identifier: Identifier = createIdentifier('designer');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    const newState = reducer.enrichedEntity(state, {
      type: 'ENRICHED_ENTITY_RECEIVED',
      enrichedEntity: enrichedEntity,
    });

    expect(newState).toEqual({
      identifier,
      labelCollection,
    });
  });

  test('I can change values of an enriched entity', () => {
    const state = {};
    const identifier: Identifier = createIdentifier('designer_updated');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer Updated'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    const newState = reducer.enrichedEntity(state, {
      type: 'ENRICHED_ENTITY_UPDATED',
      enrichedEntity,
    });

    expect(newState).toEqual({
      identifier,
      labelCollection,
    });
  });
});
