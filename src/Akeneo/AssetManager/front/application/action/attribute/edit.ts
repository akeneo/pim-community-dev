import attributeSaver from 'akeneoassetmanager/infrastructure/saver/attribute';
import {
  attributeEditionSucceeded,
  attributeEditionErrorOccured,
  attributeEditionStart as attributeEditionStartEvent,
  attributeEditionSubmission,
  attributeEditionCancel,
} from 'akeneoassetmanager/domain/event/attribute/edit';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  notifyAttributeSaveFailed,
  notifyAttributeSaveValidationError,
} from 'akeneoassetmanager/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoassetmanager/application/action/attribute/list';
import AttributeCode from 'akeneoassetmanager/domain/model/code';
import denormalizeAttribute from 'akeneoassetmanager/application/denormalizer/attribute/attribute';
import {NormalizedAttribute, Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  optionEditionSubmission,
  optionEditionErrorOccured,
  optionEditionSucceeded,
} from 'akeneoassetmanager/domain/event/attribute/option';
import {NormalizedOption, Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {AttributeWithOptions} from 'akeneoassetmanager/domain/model/attribute/type/option';
import attributeOptionSaver from 'akeneoassetmanager/infrastructure/saver/options';

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
      dispatch(notifyAttributeSaveValidationError());

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

export const saveOptions = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  if (getState().options.isSaving) {
    return;
  }

  dispatch(optionEditionSubmission());
  const normalizedAttribute = getState().attribute.data;
  const attribute = (denormalizeAttribute(normalizedAttribute) as any) as AttributeWithOptions;
  const options = getState().options.options.map((option: NormalizedOption) => Option.createFromNormalized(option));
  const updatedAttribute = attribute.setOptions(options);

  try {
    let errors = await attributeOptionSaver.save((updatedAttribute as any) as Attribute);

    if (errors) {
      const validationErrors = Object.values(
        errors.reduce((filteredErrors: {[propertyPath: string]: ValidationError}, error: ValidationError) => {
          filteredErrors[error.propertyPath] = error;

          return filteredErrors;
        }, {})
      ).map((error: ValidationError) => createValidationError(error));
      dispatch(optionEditionErrorOccured(validationErrors));

      return;
    }
  } catch (error) {
    dispatch(optionEditionErrorOccured([]));

    return;
  }

  dispatch(optionEditionSucceeded());
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
