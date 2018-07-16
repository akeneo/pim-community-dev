import {createEnrichedEntity as enrichedEntityFactory} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Identifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';
import enrichedEntitySaver from 'akeneoenrichedentity/infrastructure/saver/enriched-entity';
import {enrichedEntityCreationSucceeded, enrichedEntityCreationErrorOccured} from 'akeneoenrichedentity/domain/event/enriched-entity/create';
import { updateEnrichedEntityResults } from 'akeneoenrichedentity/application/action/enriched-entity/search';

export const createEnrichedEntity = (identifier: string, labels: {[localeCode: string]: string}) => async (dispatch: any): Promise<void> => {
  try {
    const enrichedEntity = enrichedEntityFactory(Identifier.create(identifier), LabelCollection.create(labels));
    await enrichedEntitySaver.create(enrichedEntity);
  } catch (error) {
    console.log(error);
    dispatch(enrichedEntityCreationErrorOccured(error));

    return;
  }

  dispatch(enrichedEntityCreationSucceeded());
  dispatch(updateEnrichedEntityResults());

  return;
};
