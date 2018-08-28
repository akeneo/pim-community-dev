import Saver from 'akeneoenrichedentity/domain/saver/saver';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import handleError from 'akeneoenrichedentity/infrastructure/saver/error-handler';

const routing = require('routing');

export interface EnrichedEntitySaver extends Saver<EnrichedEntity> {}

export class EnrichedEntitySaverImplementation implements EnrichedEntitySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(enrichedEntity: EnrichedEntity): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_enriched_entities_enriched_entity_edit_rest', {
        identifier: enrichedEntity.getIdentifier().stringValue(),
      }),
      enrichedEntity.normalize()
    ).catch(handleError);
  }

  async create(enrichedEntity: EnrichedEntity): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_enriched_entities_enriched_entity_create_rest'),
      enrichedEntity.normalize()
    ).catch(handleError);
  }
}

export default new EnrichedEntitySaverImplementation();
