import {enrichedEntityUpdated, enrichedEntityReceived} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import {postSave, failSave} from 'akeneoenrichedentity/application/event/form-state';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';

export const saveEnrichedEntity = (enrichedEntity: EnrichedEntity) => async (dispatch: any): Promise<void> => {
  try {
    var errors = await enrichedEntitySaver.save(enrichedEntity);

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

  dispatch(enrichedEntityReceived(savedEnrichedEntity));
};

export const updateEnrichedEntity = (enrichedEntity: EnrichedEntity) => {
  return enrichedEntityUpdated(enrichedEntity);
};
