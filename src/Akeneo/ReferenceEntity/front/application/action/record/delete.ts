import {
  notifyRecordDeleteFailed,
  notifyRecordDeletionErrorOccurred,
  notifyRecordWellDeleted,
} from 'akeneoreferenceentity/application/action/record/notify';
import recordRemover from 'akeneoreferenceentity/infrastructure/remover/record';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {updateRecordResults} from 'akeneoreferenceentity/application/action/record/search';
import {redirectToRecordIndex} from 'akeneoreferenceentity/application/action/record/router';
import {closeDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';

export const deleteRecord = (
  referenceEntityIdentifier: ReferenceEntityIdentifier,
  recordCode: RecordCode,
  updateRecordList: boolean = false
) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await recordRemover.remove(referenceEntityIdentifier, recordCode);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyRecordDeletionErrorOccurred(validationErrors));

      return;
    }

    dispatch(notifyRecordWellDeleted(recordCode));
    dispatch(redirectToRecordIndex(referenceEntityIdentifier));
    dispatch(closeDeleteModal());
    if (true === updateRecordList) {
      dispatch(updateRecordResults());
    }
  } catch (error) {
    dispatch(notifyRecordDeleteFailed());

    throw error;
  }
};
