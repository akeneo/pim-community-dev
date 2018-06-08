import LabelCollection, {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import EnrichedEntity, {createEnrichedEntity} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {enrichedEntityUpdated, enrichedEntityReceived} from 'akeneoenrichedentity/domain/event/show';

describe('akeneo > enriched entity > domain > event --- show', () => {
  test('I can create a enrichedEntityReceived event', () => {
    const identifier: Identifier = createIdentifier('designer');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    expect(enrichedEntityReceived(enrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_RECEIVED',
      enrichedEntity: enrichedEntity,
    });
  });
});

describe('akeneo > enriched entity > domain > event --- show', () => {
  test('I can create a enrichedEntityUpdated event', () => {
    const identifier: Identifier = createIdentifier('designer_updated');
    const labelCollection: LabelCollection = createLabelCollection({['en_US']: 'Designer updated'});
    const enrichedEntity: EnrichedEntity = createEnrichedEntity(identifier, labelCollection);

    expect(enrichedEntityUpdated(enrichedEntity)).toEqual({
      type: 'ENRICHED_ENTITY_UPDATED',
      enrichedEntity: enrichedEntity,
    });
  });
});
