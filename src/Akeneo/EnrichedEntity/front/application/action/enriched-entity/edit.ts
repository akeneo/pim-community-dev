import {
  enrichedEntityEditionLabelUpdated,
  enrichedEntityEditionReceived,
  enrichedEntityEditionUpdated,
} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import {postSave, failSave} from 'akeneoenrichedentity/application/event/form-state';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';

export const saveEnrichedEntity = () => async (dispatch: any, getState: any): Promise<void> => {
  const enrichedEntity = denormalizeEnrichedEntity(getState().form.data);

  try {
    const errors = await enrichedEntitySaver.save(enrichedEntity);

    if (errors) {
      dispatch(failSave(errors.map((error: ValidationError) => createValidationError(error))));

      return;
    }
  } catch (error) {
    dispatch(failSave(error));

    return;
  }

  dispatch(postSave());

  const savedEnrichedEntity: EnrichedEntity = await enrichedEntityFetcher.fetch(
    enrichedEntity.getIdentifier().stringValue()
  );

  dispatch(enrichedEntityEditionReceived(savedEnrichedEntity));
};

export const enrichedEntityLabelUpdated = (value: string, locale: string) => (dispatch: any, getState: any) => {
  dispatch(enrichedEntityEditionLabelUpdated(value, locale));
  dispatch(enrichedEntityEditionUpdated(getState().form.data));
};
