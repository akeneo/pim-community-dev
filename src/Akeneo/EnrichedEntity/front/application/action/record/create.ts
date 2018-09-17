import {notifyRecordCreateFailed, notifyRecordWellCreated} from 'akeneoenrichedentity/application/action/record/notify';
import {updateRecordResults} from 'akeneoenrichedentity/application/action/record/search';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {recordCreationErrorOccured, recordCreationSucceeded} from 'akeneoenrichedentity/domain/event/record/create';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import {createLabelCollection} from 'akeneoenrichedentity/domain/model/label-collection';
import {createCode} from 'akeneoenrichedentity/domain/model/record/code';
import {createIdentifier} from 'akeneoenrichedentity/domain/model/record/identifier';
import {createRecord as recordFactory} from 'akeneoenrichedentity/domain/model/record/record';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import recordSaver from 'akeneoenrichedentity/infrastructure/saver/record';
import {createEmptyFile} from 'akeneoenrichedentity/domain/model/file';

export const createRecord = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const enrichedEntity = getState().form.data;
  const {code, labels} = getState().createRecord.data;
  const record = recordFactory(
    createIdentifier(code),
    createEnrichedEntityIdentifier(enrichedEntity.identifier),
    createCode(code),
    createLabelCollection(labels),
    createEmptyFile()
  );

  try {
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
