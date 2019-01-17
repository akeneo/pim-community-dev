import {createReferenceEntity as referenceEntityFactory} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createLabelCollection} from 'akeneoreferenceentity/domain/model/label-collection';
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
import {createEmptyFile} from 'akeneoreferenceentity/domain/model/file';
import {createAttributeReference} from 'akeneoreferenceentity/domain/model/attribute/attribute-reference';

export const createReferenceEntity = () => async (dispatch: any, getState: () => IndexState): Promise<void> => {
  const {code, labels} = getState().create.data;
  const referenceEntity = referenceEntityFactory(
    createIdentifier(code),
    createLabelCollection(labels),
    createEmptyFile(),
    createAttributeReference(null),
    createAttributeReference(null)
  );
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
  dispatch(redirectToReferenceEntity(referenceEntity, 'attribute'));

  return;
};
