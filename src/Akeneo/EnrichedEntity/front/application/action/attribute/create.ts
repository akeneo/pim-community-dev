import {NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import attributeSaver from 'akeneoenrichedentity/infrastructure/saver/attribute';
import {
  attributeCreationSucceeded,
  attributeCreationErrorOccured,
} from 'akeneoenrichedentity/domain/event/attribute/create';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {
  notifyAttributeWellCreated,
  notifyAttributeCreateFailed,
} from 'akeneoenrichedentity/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoenrichedentity/application/action/attribute/list';
import {denormalizeMinimalAttribute} from 'akeneoenrichedentity/domain/model/attribute/minimal';
// import {attributeEditionStart} from 'akeneoenrichedentity/domain/event/attribute/edit';

export const createAttribute = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const enrichedEntity = getState().form.data;
  const formData = getState().createAttribute.data;
  const normalizedAttribute = {
    ...formData,
    identifier: {identifier: formData.code, enriched_entity_identifier: enrichedEntity.identifier},
    enriched_entity_identifier: enrichedEntity.identifier,
  } as NormalizedAttribute;
  const attribute = denormalizeMinimalAttribute(normalizedAttribute);

  try {
    let errors = await attributeSaver.create(attribute);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(attributeCreationErrorOccured(validationErrors));
      dispatch(notifyAttributeCreateFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyAttributeCreateFailed());

    return;
  }

  dispatch(attributeCreationSucceeded());
  dispatch(notifyAttributeWellCreated());
  dispatch(updateAttributeList());
  // dispatch(attributeEditionStart(attribute));

  return;
};
