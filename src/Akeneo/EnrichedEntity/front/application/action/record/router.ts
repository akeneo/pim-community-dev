import {redirectToRoute} from 'akeneoenrichedentity/application/event/router';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/identifier';

export const redirectToRecord = (record: Record) => {
  return redirectToRoute('akeneo_enriched_entities_record_edit', {
    identifier: record.getIdentifier().stringValue(),
    enrichedEntityIdentifier: record.getEnrichedEntityIdentifier().stringValue(),
    tab: 'enrich',
  });
};

export const redirectToRecordIndex = (enrichedEntityIdentifier: EnrichedEntityIdentifier) => {
  return redirectToRoute('akeneo_enriched_entities_enriched_entity_edit', {
    enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
    tab: 'records',
  });
};
