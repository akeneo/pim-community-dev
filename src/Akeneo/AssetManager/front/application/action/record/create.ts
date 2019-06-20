import {
  notifyRecordCreateFailed,
  notifyRecordWellCreated,
} from 'akeneoreferenceentity/application/action/record/notify';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {
  recordCreationErrorOccured,
  recordCreationSucceeded,
  recordCreationStart,
} from 'akeneoreferenceentity/domain/event/record/create';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/record/identifier';
import {createRecord as recordFactory} from 'akeneoreferenceentity/domain/model/record/record';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import recordSaver from 'akeneoreferenceentity/infrastructure/saver/record';
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';
import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';
import {redirectToRecord} from 'akeneoreferenceentity/application/action/record/router';
import {updateRecordResults} from 'akeneoreferenceentity/application/action/record/search';

export const createRecord = (createAnother: boolean) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
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

      return;
    }
  } catch (error) {
    dispatch(notifyRecordCreateFailed());

    return;
  }

  dispatch(notifyRecordWellCreated());
  if (createAnother) {
    dispatch(updateRecordResults());
    dispatch(recordCreationStart());
  } else {
    dispatch(recordCreationSucceeded());
    dispatch(redirectToRecord(record.getReferenceEntityIdentifier(), record.getCode()));
  }

  return;
};
