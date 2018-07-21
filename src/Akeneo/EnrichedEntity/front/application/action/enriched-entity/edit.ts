import {
  enrichedEntityEditionLabelUpdated,
  enrichedEntityEditionReceived,
  enrichedEntityEditionUpdated,
  enrichedEntityEditionImageUpdated,
  enrichedEntityEditionErrorOccured,
  enrichedEntityEditionSucceeded,
} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import {
  notifyEnrichedEntityWellSaved,
  notifyEnrichedEntitySaveFailed,
} from 'akeneoenrichedentity/application/action/enriched-entity/notify';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import Image from 'akeneoenrichedentity/domain/model/image';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';

export const saveEnrichedEntity = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const enrichedEntity = denormalizeEnrichedEntity(getState().form.data);

  try {
    const errors = await enrichedEntitySaver.save(enrichedEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(enrichedEntityEditionErrorOccured(validationErrors));
      dispatch(notifyEnrichedEntitySaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyEnrichedEntitySaveFailed());

    return;
  }

  dispatch(enrichedEntityEditionSucceeded());
  dispatch(notifyEnrichedEntityWellSaved());

  const savedEnrichedEntity: EnrichedEntity = await enrichedEntityFetcher.fetch(
    enrichedEntity.getIdentifier().stringValue()
  );

  dispatch(enrichedEntityEditionReceived(savedEnrichedEntity.normalize()));
};

export const enrichedEntityLabelUpdated = (value: string, locale: string) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(enrichedEntityEditionLabelUpdated(value, locale));
  dispatch(enrichedEntityEditionUpdated(getState().form.data));
};

export const enrichedEntityImageUpdated = (image: Image | null) => (dispatch: any, getState: () => EditState) => {
  dispatch(enrichedEntityEditionImageUpdated(image));
  dispatch(enrichedEntityEditionUpdated(getState().form.data));
};
