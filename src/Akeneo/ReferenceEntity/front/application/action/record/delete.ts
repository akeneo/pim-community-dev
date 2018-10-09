import {
  notifyRecordWellDeleted,
  notifyRecordDeleteFailed,
  notifyRecordDeletionErrorOccured,
  notifyAllRecordsWellDeleted,
  notifyAllRecordsDeletionFailed,
} from 'akeneoreferenceentity/application/action/record/notify';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import recordRemover from 'akeneoreferenceentity/infrastructure/remover/record';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {updateRecordResults} from 'akeneoreferenceentity/application/action/record/search';
import {redirectToRecordIndex} from 'akeneoreferenceentity/application/action/record/router';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';

export const deleteRecord = (record: Record) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await recordRemover.remove(record.getReferenceEntityIdentifier(), record.getCode());

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyRecordDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyRecordWellDeleted(record.getCode()));
    dispatch(redirectToRecordIndex(record.getReferenceEntityIdentifier()));
  } catch (error) {
    dispatch(notifyRecordDeleteFailed());

    throw error;
  }
};

export const deleteAllReferenceEntityRecords = (referenceEntity: ReferenceEntity) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await recordRemover.removeAll(referenceEntity.getIdentifier());

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyRecordDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyAllRecordsWellDeleted(referenceEntity.getIdentifier()));
    dispatch(updateRecordResults());
  } catch (error) {
    dispatch(notifyAllRecordsDeletionFailed());

    throw error;
  }
};
