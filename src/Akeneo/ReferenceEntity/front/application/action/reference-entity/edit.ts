import {
  referenceEntityEditionLabelUpdated,
  referenceEntityEditionReceived,
  referenceEntityEditionUpdated,
  referenceEntityEditionImageUpdated,
  referenceEntityEditionErrorOccured,
  referenceEntityEditionSucceeded,
  referenceEntityRecordCountUpdated,
} from 'akeneoreferenceentity/domain/event/reference-entity/edit';
import {
  notifyReferenceEntityWellSaved,
  notifyReferenceEntitySaveFailed,
} from 'akeneoreferenceentity/application/action/reference-entity/notify';
import {denormalizeReferenceEntity} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import referenceEntitySaver from 'akeneoreferenceentity/infrastructure/saver/reference-entity';
import referenceEntityFetcher, {
  ReferenceEntityResult,
} from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import File from 'akeneoreferenceentity/domain/model/file';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {referenceEntityPermissionChanged} from 'akeneoreferenceentity/domain/event/user';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export const saveReferenceEntity = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntity = denormalizeReferenceEntity(getState().form.data);

  try {
    const errors = await referenceEntitySaver.save(referenceEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(referenceEntityEditionErrorOccured(validationErrors));
      dispatch(notifyReferenceEntitySaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyReferenceEntitySaveFailed());

    return;
  }

  dispatch(referenceEntityEditionSucceeded());
  dispatch(notifyReferenceEntityWellSaved());

  dispatch(refreshReferenceEntity(referenceEntity.getIdentifier()));
};

export const refreshReferenceEntity = (
  referenceEntityIdentifier: ReferenceEntityIdentifier,
  refreshDataForm: boolean = false
) => async (dispatch: any): Promise<void> => {
  const referenceEntityResult: ReferenceEntityResult = await referenceEntityFetcher.fetch(referenceEntityIdentifier);
  if (refreshDataForm) {
    dispatch(referenceEntityRecordCountUpdated(referenceEntityResult.recordCount));
  }
  dispatch(referenceEntityEditionReceived(referenceEntityResult.referenceEntity.normalize()));
  dispatch(referenceEntityPermissionChanged(referenceEntityResult.permission));
};

export const referenceEntityLabelUpdated = (value: string, locale: string) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(referenceEntityEditionLabelUpdated(value, locale));
  dispatch(referenceEntityEditionUpdated(getState().form.data));
};

export const referenceEntityImageUpdated = (image: File) => (dispatch: any, getState: () => EditState) => {
  dispatch(referenceEntityEditionImageUpdated(image));
  dispatch(referenceEntityEditionUpdated(getState().form.data));
};
