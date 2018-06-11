import Fetcher from 'akeneoenrichedentity/domain/fetcher/fetcher';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import hydrator from 'akeneoenrichedentity/application/hydrator/enriched-entity';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';

const routing = require('routing');

export interface EnrichedEntityFetcher extends Fetcher<EnrichedEntity> {}

export class EnrichedEntityFetcherImplementation implements EnrichedEntityFetcher {
  constructor(private hydrator: (backendEnrichedEntity: any) => EnrichedEntity) {
    Object.freeze(this);
  }

  async fetch(identifier: string): Promise<EnrichedEntity> {
    const backendEnrichedEntity = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_get_rest', {identifier})
    );

    return this.hydrator(backendEnrichedEntity);
  }

  async fetchAll(): Promise<EnrichedEntity[]> {
    const backendEnrichedEntities = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_index_rest')
    );

    return hydrateAll<EnrichedEntity>(this.hydrator)(backendEnrichedEntities);
  }

  async search(): Promise<{items: EnrichedEntity[]; total: number}> {
    const backendEnrichedEntities = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entities_index_rest')
    );

    const items = hydrateAll<EnrichedEntity>(this.hydrator)(backendEnrichedEntities.items);

    return {
      items,
      total: backendEnrichedEntities.total,
    };
  }
}

export default new EnrichedEntityFetcherImplementation(hydrator);
