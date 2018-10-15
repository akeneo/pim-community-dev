import {
  referenceEntityEditionLabelUpdated,
  referenceEntityEditionReceived,
  referenceEntityEditionUpdated,
  referenceEntityEditionImageUpdated,
  referenceEntityEditionErrorOccured,
  referenceEntityEditionSucceeded,
} from 'akeneoreferenceentity/domain/event/reference-entity/edit';
import {
  notifyReferenceEntityWellSaved,
  notifyReferenceEntitySaveFailed,
} from 'akeneoreferenceentity/application/action/reference-entity/notify';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import referenceEntitySaver from 'akeneoreferenceentity/infrastructure/saver/reference-entity';
import referenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ValidationError, {createValidationError} from 'akeneoreferenceentity/domain/model/validation-error';
import File from 'akeneoreferenceentity/domain/model/file';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';

export const saveReferenceEntity = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const referenceEntity = denormalizeReferenceEntity(getState().form.data);

  try {
    const errors = await referenceEntitySaver.save(referenceEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(referenceEntityEditionErrorOccured(validationErrors));
      dispatch(notifyReferenceEntitySaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyReferenceEntitySaveFailed());

    return;
  }

  dispatch(referenceEntityEditionSucceeded());
  dispatch(notifyReferenceEntityWellSaved());

  const savedReferenceEntity: ReferenceEntity = await referenceEntityFetcher.fetch(referenceEntity.getIdentifier());

  dispatch(referenceEntityEditionReceived(savedReferenceEntity.normalize()));
};

export const referenceEntityLabelUpdated = (value: string, locale: string) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(referenceEntityEditionLabelUpdated(value, locale));
  dispatch(referenceEntityEditionUpdated(getState().form.data));
};

export const referenceEntityImageUpdated = (image: File) => (dispatch: any, getState: () => EditState) => {
  dispatch(referenceEntityEditionImageUpdated(image));
  dispatch(referenceEntityEditionUpdated(getState().form.data));
};
