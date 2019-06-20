import {redirectToRoute} from 'akeneoreferenceentity/application/event/router';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export const redirectToReferenceEntity = (referenceEntityIdentifier: ReferenceEntityIdentifier, tab: string) => {
  return redirectToRoute('akeneo_reference_entities_reference_entity_edit', {
    identifier: referenceEntityIdentifier.stringValue(),
    tab,
  });
};

export const redirectToReferenceEntityListItem = () => {
  return redirectToRoute('akeneo_reference_entities_reference_entity_index');
};
