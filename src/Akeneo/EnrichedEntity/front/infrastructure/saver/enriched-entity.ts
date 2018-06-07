import Saver from 'akeneoenrichedentity/domain/saver/saver';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {postJSON} from 'akeneoenrichedentity/tools/fetch';
import hidrator from 'akeneoenrichedentity/application/hidrator/enriched-entity';

const routing = require('routing');

export interface EnrichedEntitySaver extends Saver<EnrichedEntity> {}

export class EnrichedEntitySaverImplementation implements EnrichedEntitySaver {
  constructor(private hidrator: (backendEnrichedEntity: any) => EnrichedEntity) {
    Object.freeze(this);
  }

  async save(enrichedEntity: EnrichedEntity): Promise<EnrichedEntity> {
    const backendEnrichedEntity = await postJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_edit_rest', {
        identifier: 'designer',
      }),
      {
        identifier: enrichedEntity.getIdentifier().stringValue(),
        labels: enrichedEntity.getLabelCollection().getLabels(),
      }
    );

    return this.hidrator(backendEnrichedEntity);
  }
}

export default new EnrichedEntitySaverImplementation(hidrator);
