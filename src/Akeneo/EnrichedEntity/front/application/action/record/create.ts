import {createRecord as recordFactory} from 'akeneoenrichedentity/domain/model/record/record';
import RecordIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import recordSaver from 'akeneoenrichedentity/infrastructure/saver/record';
import {recordCreationSucceeded, recordCreationErrorOccured} from 'akeneoenrichedentity/domain/event/record/create';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {updateRecordResults} from 'akeneoenrichedentity/application/action/record/search';

export const createRecord = (recordCode: string, labels: {[localeCode: string]: string}) => async (
  dispatch: any,
  getState: any
): Promise<void> => {
  try {
    const {enrichedEntity} = getState();
    const record = recordFactory(
      RecordIdentifier.create(recordCode),
      enrichedEntity.getIdentifier(),
      LabelCollection.create(labels)
    );
    let errors = await recordSaver.create(record);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(recordCreationErrorOccured(validationErrors));

      return;
    }
  } catch (error) {
    dispatch(recordCreationErrorOccured(error));

    return;
  }

  dispatch(recordCreationSucceeded());
  dispatch(updateRecordResults());

  return;
};
