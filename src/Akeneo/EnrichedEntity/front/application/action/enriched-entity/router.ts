import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {redirectToRoute} from 'akeneoenrichedentity/application/event/router';

export const redirectToEnrichedEntity = (enrichedEntity: EnrichedEntity) => {
  return redirectToRoute('akeneo_enriched_entities_enriched_entity_edit', {
    identifier: enrichedEntity.getIdentifier().stringValue(),
  });
};

export const redirectToEnrichedEntityIndex = () => {
  return redirectToRoute('akeneo_enriched_entities_enriched_entity_index', {});
};
