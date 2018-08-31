import {redirectToRoute} from 'akeneoenrichedentity/application/event/router';
import Record from 'akeneoenrichedentity/domain/model/record/record';

export const redirectToRecord = (record: Record) => {
  return redirectToRoute('akeneo_enriched_entities_record_edit', {
    identifier: record.getIdentifier().identifier,
    enrichedEntityIdentifier: record.getEnrichedEntityIdentifier(),
  });
};
