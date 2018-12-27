import {
  // permissionEditionReceived,
  permissionEditionErrorOccured,
  permissionEditionSucceeded,
} from 'akeneoreferenceentity/domain/event/reference-entity/permission';
import {
  notifyPermissionWellSaved,
  notifyPermissionSaveFailed,
} from 'akeneoreferenceentity/application/action/reference-entity/notify';
import permissionSaver from 'akeneoreferenceentity/infrastructure/saver/permission';
// import referenceEntityFetcher, {
//   ReferenceEntityResult,
// } from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export const savePermission = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntityIdentifier = ReferenceEntityIdentifier.create(getState().form.data.identifier);
  const permission = getState().permission.data;

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

  // const referenceEntityResult: ReferenceEntityResult = await referenceEntityFetcher.fetch(
  //   referenceEntity.getIdentifier()
  // );
  // dispatch(referenceEntityRecordCountUpdated(referenceEntityResult.recordCount));
  // dispatch(permissionEditionReceived(referenceEntityResult.referenceEntity.normalize()));
};
