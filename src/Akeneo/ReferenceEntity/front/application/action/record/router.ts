import {redirectToRoute} from 'akeneoreferenceentity/application/event/router';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';

export const redirectToRecord = (record: Record) => {
  return redirectToRoute('akeneo_reference_entities_record_edit', {
    recordCode: record.getCode().stringValue(),
    referenceEntityIdentifier: record.getReferenceEntityIdentifier().stringValue(),
    tab: 'enrich',
  });
};

export const redirectToRecordIndex = (referenceEntityIdentifier: ReferenceEntityIdentifier) => {
  return redirectToRoute('akeneo_reference_entities_reference_entity_edit', {
    identifier: referenceEntityIdentifier.stringValue(),
    tab: 'record',
  });
};
