import Fetcher from 'akeneoenrichedentity/application/fetcher/fetcher';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import hidrator from 'akeneoenrichedentity/application/hidrator/enriched-entity';
import hidrateAll from 'akeneoenrichedentity/application/hidrator/hidrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';

const routing = require('routing');

export interface EnrichedEntityFetcher extends Fetcher<EnrichedEntity> {}

export class EnrichedEntityFetcherImplementation implements EnrichedEntityFetcher {
  constructor(private hidrator: (backendEnrichedEntity: any) => EnrichedEntity) {
    Object.freeze(this);
  }

  async fetch(identifier: string): Promise<EnrichedEntity> {
    const backendEnrichedEntity = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_get_rest', {identifier})
    );

    return this.hidrator(backendEnrichedEntity);
  }

  async fetchAll(): Promise<EnrichedEntity[]> {
    const backendEnrichedEntities = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_index_rest')
    );

    return hidrateAll<EnrichedEntity>(this.hidrator)(backendEnrichedEntities);
  }

  async search(): Promise<{items: EnrichedEntity[]; total: number}> {
    const backendEnrichedEntities = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_index_rest')
    );

    const items = hidrateAll<EnrichedEntity>(this.hidrator)(backendEnrichedEntities.items);

    return {
      items,
      total: backendEnrichedEntities.total,
    };
  }
}

export default new EnrichedEntityFetcherImplementation(hidrator);
