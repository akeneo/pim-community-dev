import {
  recordEditionLabelUpdated,
  recordEditionReceived,
  recordEditionImageUpdated,
  recordEditionErrorOccured,
  recordEditionSucceeded,
} from 'akeneoenrichedentity/domain/event/record/edit';
import {
  notifyRecordWellSaved,
  notifyRecordSaveFailed,
  notifyRecordWellDeleted,
  notifyRecordDeleteFailed,
  notifyRecordDeletionErrorOccured,
} from 'akeneoenrichedentity/application/action/record/notify';
import Record, {denormalizeRecord} from 'akeneoenrichedentity/domain/model/record/record';
import recordSaver from 'akeneoenrichedentity/infrastructure/saver/record';
import recordRemover from 'akeneoenrichedentity/infrastructure/remover/record';
import recordFetcher from 'akeneoenrichedentity/infrastructure/fetcher/record';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import File from 'akeneoenrichedentity/domain/model/file';
import {EditState} from 'akeneoenrichedentity/application/reducer/record/edit';
import {redirectToRecordIndex} from 'akeneoenrichedentity/application/action/record/router';

export const saveRecord = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const record = denormalizeRecord(getState().form.data);

  try {
    const errors = await recordSaver.save(record);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(recordEditionErrorOccured(validationErrors));
      dispatch(notifyRecordSaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyRecordSaveFailed());

    return;
  }

  dispatch(recordEditionSucceeded());
  dispatch(notifyRecordWellSaved());
  const savedRecord: Record = await recordFetcher.fetch(record.getEnrichedEntityIdentifier(), record.getCode());

  dispatch(recordEditionReceived(savedRecord));
};

export const deleteRecord = (record: Record) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await recordRemover.remove(record.getEnrichedEntityIdentifier(), record.getIdentifier());

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyRecordDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyRecordWellDeleted());
    dispatch(redirectToRecordIndex(record.getEnrichedEntityIdentifier()));
  } catch (error) {
    dispatch(notifyRecordDeleteFailed());

    throw error;
  }
};

export const recordLabelUpdated = (value: string, locale: string) => (dispatch: any) => {
  dispatch(recordEditionLabelUpdated(value, locale));
};

export const recordImageUpdated = (image: File) => (dispatch: any) => {
  dispatch(recordEditionImageUpdated(image));
};
