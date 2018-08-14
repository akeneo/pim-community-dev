import {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
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
// import {attributeEditionStart} from 'akeneoenrichedentity/domain/event/attribute/edit';

export const createAttribute = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const enrichedEntity = getState().form.data;
  const formData = getState().createAttribute.data;
  const normalizedAttribute = {
    ...formData,
    order: 0,
    required: false,
    maxLength: null,
    maxFileSize: null,
    allowedExtensions: [],
    identifier: {identifier: formData.code, enrichedEntityIdentifier: enrichedEntity.identifier},
    enrichedEntityIdentifier: enrichedEntity.identifier,
  };
  const attribute = denormalizeAttribute(normalizedAttribute);

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

// export const saveAttribute = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
//   const enrichedEntity = getState().form.data;
//   const formData = getState().createAttribute.data;
//   const normalizedAttribute = {
//     ...formData,
//     order: 0,
//     required: false,
//     maxLength:
//       undefined === formData.additionalProperties['maxLength'] ? null : formData.additionalProperties['maxLength'],
//     maxFileSize:
//       undefined === formData.additionalProperties['maxFileSize'] ? null : formData.additionalProperties['maxFileSize'],
//     extensions:
//       undefined === formData.additionalProperties['extensions'] ? [] : formData.additionalProperties['extensions'],
//     identifier: {identifier: formData.code, enrichedEntityIdentifier: enrichedEntity.identifier},
//     enrichedEntityIdentifier: enrichedEntity.identifier,
//   };
//   const attribute = denormalizeAttribute(normalizedAttribute);

//   try {
//     let errors = await attributeSaver.create(attribute);

//     if (errors) {
//       const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
//       dispatch(attributeCreationErrorOccured(validationErrors));
//       dispatch(notifyAttributeCreateFailed());

//       return;
//     }
//   } catch (error) {
//     dispatch(notifyAttributeCreateFailed());

//     return;
//   }

//   dispatch(attributeCreationSucceeded());
//   dispatch(notifyAttributeWellCreated());
//   dispatch(updateAttributeList());

//   return;
// };
