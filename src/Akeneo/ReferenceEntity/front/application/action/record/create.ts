import {
  notifyRecordCreateFailed,
  notifyRecordWellCreated,
} from 'akeneoreferenceentity/application/action/record/notify';
import {updateRecordResults} from 'akeneoreferenceentity/application/action/record/search';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {recordCreationErrorOccured, recordCreationSucceeded} from 'akeneoreferenceentity/domain/event/record/create';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';
import {createRecord as recordFactory} from 'akeneoreferenceentity/domain/model/record/record';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import recordSaver from 'akeneoreferenceentity/infrastructure/saver/record';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';
import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';

export const createRecord = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntity = getState().form.data;
  const {code, labels} = getState().createRecord.data;
  const record = recordFactory(
    createIdentifier(code),
    createReferenceEntityIdentifier(referenceEntity.identifier),
    createCode(code),
    createLabelCollection(labels),
    createEmptyFile(),
    createValueCollection([])
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
