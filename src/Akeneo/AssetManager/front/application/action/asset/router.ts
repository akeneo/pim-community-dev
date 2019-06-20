import {redirectToRoute} from 'akeneoreferenceentity/application/event/router';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';

export const redirectToRecord = (referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode) => {
  return redirectToRoute('akeneo_reference_entities_record_edit', {
    recordCode: recordCode.stringValue(),
    referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
    tab: 'enrich',
  });
};

export const redirectToRecordIndex = (referenceEntityIdentifier: ReferenceEntityIdentifier) => {
  return redirectToRoute('akeneo_reference_entities_reference_entity_edit', {
    identifier: referenceEntityIdentifier.stringValue(),
    tab: 'record',
  });
};
