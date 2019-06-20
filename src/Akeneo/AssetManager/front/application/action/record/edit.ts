import {
  recordEditionLabelUpdated,
  recordEditionReceived,
  recordEditionImageUpdated,
  recordEditionErrorOccured,
  recordEditionSucceeded,
  recordEditionValueUpdated,
  recordEditionUpdated,
  recordEditionSubmission,
} from 'akeneoreferenceentity/domain/event/record/edit';
import {
  notifyRecordWellSaved,
  notifyRecordSaveFailed,
  notifyRecordSaveValidationError,
} from 'akeneoreferenceentity/application/action/record/notify';
import recordSaver from 'akeneoreferenceentity/infrastructure/saver/record';
import recordFetcher, {RecordResult} from 'akeneoreferenceentity/infrastructure/fetcher/record';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import File from 'akeneoreferenceentity/domain/model/file';
import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import {redirectToRecordIndex} from 'akeneoreferenceentity/application/action/record/router';
import denormalizeRecord from 'akeneoreferenceentity/application/denormalizer/record';
import Value from 'akeneoreferenceentity/domain/model/record/value';

export const saveRecord = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const record = denormalizeRecord(getState().form.data);

  dispatch(recordEditionSubmission());
  try {
    const errors = await recordSaver.save(record);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(recordEditionErrorOccured(validationErrors));
      dispatch(notifyRecordSaveValidationError());

      return;
    }
  } catch (error) {
    dispatch(notifyRecordSaveFailed());

    return;
  }

  dispatch(recordEditionSucceeded());
  dispatch(notifyRecordWellSaved());
  const savedRecord: RecordResult = await recordFetcher.fetch(record.getReferenceEntityIdentifier(), record.getCode());

  dispatch(recordEditionReceived(savedRecord.record));
};

export const recordLabelUpdated = (value: string, locale: string) => (dispatch: any, getState: any) => {
  dispatch(recordEditionLabelUpdated(value, locale));
  dispatch(recordEditionUpdated(getState().form.data));
};

export const recordImageUpdated = (image: File) => (dispatch: any, getState: any) => {
  dispatch(recordEditionImageUpdated(image));
  dispatch(recordEditionUpdated(getState().form.data));
};

export const recordValueUpdated = (value: Value) => (dispatch: any, getState: any) => {
  dispatch(recordEditionValueUpdated(value));
  dispatch(recordEditionUpdated(getState().form.data));
};

export const backToReferenceEntity = () => (dispatch: any, getState: any) => {
  const record = denormalizeRecord(getState().form.data);
  dispatch(redirectToRecordIndex(record.getReferenceEntityIdentifier()));
};
