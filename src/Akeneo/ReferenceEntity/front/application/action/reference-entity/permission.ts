import {
  permissionEditionErrorOccured,
  permissionEditionSucceeded,
  permissionEditionReceived,
} from 'akeneoreferenceentity/domain/event/reference-entity/permission';
import {
  notifyPermissionWellSaved,
  notifyPermissionSaveFailed,
} from 'akeneoreferenceentity/application/action/reference-entity/notify';
import permissionSaver from 'akeneoreferenceentity/infrastructure/saver/permission';
import permissionFetcher from 'akeneoreferenceentity/infrastructure/fetcher/permission';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {denormalizePermissionCollection} from 'akeneoreferenceentity/domain/model/reference-entity/permission';
import {refreshReferenceEntity} from 'akeneoreferenceentity/application/action/reference-entity/edit';

export const savePermission = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntityIdentifier = ReferenceEntityIdentifier.create(getState().form.data.identifier);
  const permission = denormalizePermissionCollection(getState().permission.data);

  try {
    const errors = await permissionSaver.save(referenceEntityIdentifier, permission);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(permissionEditionErrorOccured(validationErrors));
      dispatch(notifyPermissionSaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyPermissionSaveFailed());

    return;
  }

  dispatch(permissionEditionSucceeded());
  dispatch(notifyPermissionWellSaved());

  const updatedPermission = await permissionFetcher.fetch(referenceEntityIdentifier);
  dispatch(permissionEditionReceived(updatedPermission));
  dispatch(refreshReferenceEntity(referenceEntityIdentifier, false));
};
