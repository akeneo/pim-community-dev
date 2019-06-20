import {
  notifyReferenceEntityWellDeleted,
  notifyReferenceEntityDeleteFailed,
  notifyReferenceEntityDeletionErrorOccured,
} from 'akeneoreferenceentity/application/action/reference-entity/notify';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import referenceEntityRemover from 'akeneoreferenceentity/infrastructure/remover/reference-entity';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import {redirectToReferenceEntityListItem} from 'akeneoreferenceentity/application/action/reference-entity/router';
import {closeDeleteModal} from 'akeneoreferenceentity/application/event/confirmDelete';

export const deleteReferenceEntity = (referenceEntity: ReferenceEntity) => async (dispatch: any): Promise<void> => {
  try {
    const errors = await referenceEntityRemover.remove(referenceEntity.getIdentifier());

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(notifyReferenceEntityDeletionErrorOccured(validationErrors));

      return;
    }

    dispatch(notifyReferenceEntityWellDeleted());
    dispatch(redirectToReferenceEntityListItem());
    dispatch(closeDeleteModal());
  } catch (error) {
    dispatch(notifyReferenceEntityDeleteFailed());

    throw error;
  }
};
