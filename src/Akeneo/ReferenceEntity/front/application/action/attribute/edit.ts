import attributeSaver from 'akeneoreferenceentity/infrastructure/saver/attribute';
import {
  attributeEditionSucceeded,
  attributeEditionErrorOccured,
  attributeEditionStart as attributeEditionStartEvent,
  attributeEditionSubmission,
  attributeEditionCancel,
} from 'akeneoreferenceentity/domain/event/attribute/edit';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {notifyAttributeSaveFailed} from 'akeneoreferenceentity/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoreferenceentity/application/action/attribute/list';
import AttributeCode from 'akeneoreferenceentity/domain/model/code';
import denormalizeAttribute from 'akeneoreferenceentity/application/denormalizer/attribute/attribute';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export const saveAttribute = (dismiss: boolean = true) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  if (getState().attribute.isSaving) {
    return;
  }

  dispatch(attributeEditionSubmission());
  const normalizedAttribute = getState().attribute.data;
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
    dispatch(attributeEditionErrorOccured([]));
    dispatch(notifyAttributeSaveFailed());

    return;
  }

  dispatch(attributeEditionSucceeded());
  if (dismiss) {
    dispatch(attributeEditionCancel());
  }
  await dispatch(updateAttributeList());

  return;
};

export const attributeEditionStartByCode = (attributeCode: AttributeCode) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const state = getState();
  if (null === state.attributes.attributes) {
    return;
  }

  const attributeToEdit = state.attributes.attributes.find(
    (attribute: NormalizedAttribute) => attribute.code === attributeCode.stringValue()
  );

  dispatch(attributeEditionStart(attributeToEdit));
};

export const attributeEditionStartByIdentifier = (attributeIdentifier: AttributeIdentifier) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const state = getState();
  if (null === state.attributes.attributes) {
    return;
  }

  const attributeToEdit = state.attributes.attributes.find(
    (attribute: NormalizedAttribute) => attribute.identifier === attributeIdentifier.stringValue()
  );

  dispatch(attributeEditionStart(attributeToEdit));
};

export const attributeEditionStart = (attribute: NormalizedAttribute | undefined) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  if (undefined === attribute) {
    return;
  }

  const attributeState = getState().attribute;

  if (attributeState.isDirty) {
    await dispatch(saveAttribute(false));
  }

  if (!getState().attribute.isDirty) {
    dispatch(attributeEditionStartEvent(denormalizeAttribute(attribute)));
  }
};
