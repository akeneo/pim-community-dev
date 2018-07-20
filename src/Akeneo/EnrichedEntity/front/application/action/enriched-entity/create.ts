import {createEnrichedEntity as enrichedEntityFactory} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import {
  enrichedEntityCreationSucceeded,
  enrichedEntityCreationErrorOccured,
} from 'akeneoenrichedentity/domain/event/enriched-entity/create';
import {updateEnrichedEntityResults} from 'akeneoenrichedentity/application/action/enriched-entity/search';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';

export const createEnrichedEntity = (identifier: string, labels: {[localeCode: string]: string}) => async (
  dispatch: any
): Promise<void> => {
  try {
    const enrichedEntity = enrichedEntityFactory(Identifier.create(identifier), LabelCollection.create(labels));
    let errors = await enrichedEntitySaver.create(enrichedEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(enrichedEntityCreationErrorOccured(validationErrors));

      return;
    }
  } catch (error) {
    dispatch(enrichedEntityCreationErrorOccured(error));

    return;
  }

  dispatch(enrichedEntityCreationSucceeded());
  dispatch(updateEnrichedEntityResults());

  return;
};
