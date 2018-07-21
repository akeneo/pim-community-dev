import {createEnrichedEntity as enrichedEntityFactory} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import {
  enrichedEntityCreationSucceeded,
  enrichedEntityCreationErrorOccured,
} from 'akeneoenrichedentity/domain/event/enriched-entity/create';
import {
  notifyEnrichedEntityWellCreated,
  notifyEnrichedEntityCreateFailed,
} from 'akeneoenrichedentity/application/action/enriched-entity/notify';
import {updateEnrichedEntityResults} from 'akeneoenrichedentity/application/action/enriched-entity/search';
import ValidationError, {createValidationError} from 'akeneoenrichedentity/domain/model/validation-error';
import {IndexState} from 'akeneoenrichedentity/application/reducer/enriched-entity/index';

export const createEnrichedEntity = () => async (dispatch: any, getState: () => IndexState): Promise<void> => {
  try {
    const {code, labels} = getState().create.data;
    const enrichedEntity = enrichedEntityFactory(Identifier.create(code), LabelCollection.create(labels));
    let errors = await enrichedEntitySaver.create(enrichedEntity);

    if (errors) {
      const validationErrors = errors.map((error: ValidationError) => createValidationError(error));
      dispatch(enrichedEntityCreationErrorOccured(validationErrors));
      dispatch(notifyEnrichedEntityCreateFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyEnrichedEntityCreateFailed());

    return;
  }

  dispatch(enrichedEntityCreationSucceeded());
  dispatch(notifyEnrichedEntityWellCreated());
  dispatch(updateEnrichedEntityResults());

  return;
};
