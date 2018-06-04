import Saver from 'akeneoenrichedentity/domain/saver/saver';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

export interface EnrichedEntitySaver extends Saver<EnrichedEntity> {}

export class EnrichedEntitySaverImplementation implements EnrichedEntitySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(enrichedEntity: EnrichedEntity): Promise<EnrichedEntity> {
    console.log('save enrich entity : ', enrichedEntity);
    // const backendEnrichedEntity = await getJSON(
    //   routing.generate('akeneo_enriched_entities_enriched_entities_get_rest', {enrichedEntity.getIdentifier().stringValue()})
    // );
    //
    return enrichedEntity;
  }
}

export default new EnrichedEntitySaverImplementation();
