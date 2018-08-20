import {denormalizeAttribute, NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import attributeSaver from 'akeneoenrichedentity/infrastructure/saver/attribute';
import {
  attributeEditionSucceeded,
  attributeEditionErrorOccured,
} from 'akeneoenrichedentity/domain/event/attribute/edit';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {
  notifyAttributeWellSaved,
  notifyAttributeSaveFailed,
} from 'akeneoenrichedentity/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoenrichedentity/application/action/attribute/list';

export const saveAttribute = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const enrichedEntity = getState().form.data;
  const formData = getState().attribute.data as NormalizedAttribute;
  const normalizedAttribute = {
    ...formData,
    identifier: {identifier: formData.code, enriched_entity_identifier: enrichedEntity.identifier},
    enrichedEntityIdentifier: enrichedEntity.identifier,
  };
  const attribute = denormalizeAttribute(normalizedAttribute);

  try {
    let errors = await attributeSaver.save(attribute);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(attributeEditionErrorOccured(validationErrors));
      dispatch(notifyAttributeSaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyAttributeSaveFailed());

    return;
  }

  dispatch(attributeEditionSucceeded());
  dispatch(notifyAttributeWellSaved());
  dispatch(updateAttributeList());

  return;
};
