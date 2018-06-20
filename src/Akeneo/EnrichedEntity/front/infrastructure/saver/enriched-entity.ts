import Saver from 'akeneoenrichedentity/domain/saver/saver';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import hydrator from 'akeneoenrichedentity/application/hydrator/enriched-entity';

const routing = require('routing');

export interface EnrichedEntitySaver extends Saver<EnrichedEntity> {}

export class EnrichedEntitySaverImplementation implements EnrichedEntitySaver {
  constructor(private hydrator: (backendEnrichedEntity: any) => EnrichedEntity) {
    Object.freeze(this);
  }

  async save(enrichedEntity: EnrichedEntity): Promise<EnrichedEntity> {
    const backendEnrichedEntity = await postJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_edit_rest', {
        identifier: enrichedEntity.getIdentifier().stringValue(),
      }),
      enrichedEntity.normalize()
    );

    return this.hydrator(backendEnrichedEntity);
  }
}

export default new EnrichedEntitySaverImplementation(hydrator);
