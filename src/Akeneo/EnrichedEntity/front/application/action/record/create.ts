import {createRecord as recordFactory} from 'akeneoenrichedentity/domain/model/record/record';
import RecordIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import recordSaver from 'akeneoenrichedentity/infrastructure/saver/record';
import {recordCreationSucceeded, recordCreationErrorOccured} from 'akeneoenrichedentity/domain/event/record/create';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {updateRecordResults} from 'akeneoenrichedentity/application/action/record/search';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {
  notifyRecordWellCreated,
  notifyRecordCreateFailed,
} from 'akeneoenrichedentity/application/action/record/notify';

export const createRecord = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  try {
    const enrichedEntity = getState().form.data;
    const {code, labels} = getState().createRecord.data;

    const record = recordFactory(
      RecordIdentifier.create(code),
      EnrichedEntityIdentifier.create(enrichedEntity.identifier),
      LabelCollection.create(labels)
    );
    let errors = await recordSaver.create(record);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(recordCreationErrorOccured(validationErrors));
      dispatch(notifyRecordCreateFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyRecordCreateFailed());

    return;
  }

  dispatch(recordCreationSucceeded());
  dispatch(notifyRecordWellCreated());
  dispatch(updateRecordResults());

  return;
};
