import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {redirectToRoute} from 'akeneoreferenceentity/application/event/router';

export const redirectToReferenceEntity = (referenceEntity: ReferenceEntity, tab: string) => {
  return redirectToRoute('akeneo_reference_entities_reference_entity_edit', {
    identifier: referenceEntity.getIdentifier().stringValue(),
    tab,
  });
};

export const redirectToReferenceEntityIndex = () => {
  return redirectToRoute('akeneo_reference_entities_reference_entity_index');
};
