import referenceEntitySaver from 'akeneoreferenceentity/infrastructure/saver/reference-entity';
import {
  referenceEntityCreationSucceeded,
  referenceEntityCreationErrorOccured,
} from 'akeneoreferenceentity/domain/event/reference-entity/create';
import {
  notifyReferenceEntityWellCreated,
  notifyReferenceEntityCreateFailed,
} from 'akeneoreferenceentity/application/action/reference-entity/notify';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {IndexState} from 'akeneoreferenceentity/application/reducer/reference-entity/index';
import {redirectToReferenceEntity} from 'akeneoreferenceentity/application/action/reference-entity/router';
import {denormalizeReferenceEntityCreation} from 'akeneoreferenceentity/domain/model/reference-entity/creation';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

export const createReferenceEntity = () => async (dispatch: any, getState: () => IndexState): Promise<void> => {
  const referenceEntity = denormalizeReferenceEntityCreation(getState().create.data);

  try {
    let errors = await referenceEntitySaver.create(referenceEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(referenceEntityCreationErrorOccured(validationErrors));

      return;
    }
  } catch (error) {
    dispatch(notifyReferenceEntityCreateFailed());

    return;
  }

  dispatch(referenceEntityCreationSucceeded());
  dispatch(notifyReferenceEntityWellCreated());
  dispatch(redirectToReferenceEntity(createIdentifier(referenceEntity.getCode().stringValue()), 'attribute'));

  return;
};
