import attributeSaver from 'akeneoreferenceentity/infrastructure/saver/attribute';
import {
  attributeCreationSucceeded,
  attributeCreationErrorOccured,
} from 'akeneoreferenceentity/domain/event/attribute/create';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {
  notifyAttributeWellCreated,
  notifyAttributeCreateFailed,
  notifyAttributeCreateValidationError,
} from 'akeneoreferenceentity/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoreferenceentity/application/action/attribute/list';
import {
  denormalizeMinimalAttribute,
  MinimalNormalizedAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import {attributeEditionStartByCode} from 'akeneoreferenceentity/application/action/attribute/edit';

export const createAttribute = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntity = getState().form.data;
  const formData = getState().createAttribute.data;
  const normalizedAttribute = {
    ...formData,
    reference_entity_identifier: referenceEntity.identifier,
  } as MinimalNormalizedAttribute;
  const attribute = denormalizeMinimalAttribute(normalizedAttribute);

  try {
    let errors = await attributeSaver.create(attribute);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(attributeCreationErrorOccured(validationErrors));
      dispatch(notifyAttributeCreateValidationError());

      return;
    }
  } catch (error) {
    dispatch(notifyAttributeCreateFailed());

    return;
  }

  dispatch(attributeCreationSucceeded());
  dispatch(notifyAttributeWellCreated());
  await dispatch(updateAttributeList());
  dispatch(attributeEditionStartByCode(attribute.code));

  return;
};
