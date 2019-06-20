import attributeSaver from 'akeneoassetmanager/infrastructure/saver/attribute';
import {
  attributeCreationSucceeded,
  attributeCreationErrorOccured,
} from 'akeneoassetmanager/domain/event/attribute/create';
import ValidationError, {createValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  notifyAttributeWellCreated,
  notifyAttributeCreateFailed,
  notifyAttributeCreateValidationError,
} from 'akeneoassetmanager/application/action/attribute/notify';
import {updateAttributeList} from 'akeneoassetmanager/application/action/attribute/list';
import {
  denormalizeMinimalAttribute,
  MinimalNormalizedAttribute,
} from 'akeneoassetmanager/domain/model/attribute/minimal';
import {attributeEditionStartByCode} from 'akeneoassetmanager/application/action/attribute/edit';

export const createAttribute = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = getState().form.data;
  const formData = getState().createAttribute.data;
  const normalizedAttribute = {
    ...formData,
    asset_family_identifier: assetFamily.identifier,
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
