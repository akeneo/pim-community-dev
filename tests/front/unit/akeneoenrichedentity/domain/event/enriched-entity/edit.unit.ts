import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {enrichedEntitySaved, enrichedEntityReceived, enrichedEntityUpdated} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';

describe('akeneo > enriched entity > domain > event > enriched entity --- edit', () => {
  test('I can create a enrichedEntityReceived event', () => {
    const identifier: Identifier = createIdentifier('designer');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    expect(enrichedEntityReceived(enrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_RECEIVED',
      enrichedEntity: enrichedEntity,
    });
  });

  test('I can create a enrichedEntitySaved event', () => {
    const identifier: Identifier = createIdentifier('designer_saved');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer saved'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    expect(enrichedEntitySaved(enrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_SAVED',
      enrichedEntity,
    });
  });

  test('I can create a enrichedEntityUpdated event', () => {
    const identifier: Identifier = createIdentifier('designer_updated');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer updated'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    expect(enrichedEntityUpdated(enrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_UPDATED',
      enrichedEntity,
    });
  });
});
