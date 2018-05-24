import Fetcher from 'akeneoenrichedentity/application/fetcher/fetcher';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import hidrator from 'akeneoenrichedentity/application/hidrator/enriched-entity';
import hidrateAll from 'akeneoenrichedentity/application/hidrator/hidrator';

const routing = require('routing');

export interface EnrichedEntityFetcher extends Fetcher<EnrichedEntity> {}

export class EnrichedEntityFetcherImplementation implements EnrichedEntityFetcher {
  constructor(private hidrator: (backendEnrichedEntity: any) => EnrichedEntity) {
    Object.freeze(this);
  }

  async fetch(identifier: string): Promise<EnrichedEntity> {
    console.log(identifier);

    return (await this.fetchAll())[0];
  }

  async fetchAll(): Promise<EnrichedEntity[]> {
    const backendEnrichedEntities = await fetch(
      routing.generate('akeneo_enriched_entities_enriched_entities_index_rest')
    );

    return hidrateAll<EnrichedEntity>(this.hidrator)(backendEnrichedEntities);
  }

  async search(): Promise<{items: EnrichedEntity[]; total: number}> {
    const response = await fetch(routing.generate('akeneo_enriched_entities_enriched_entities_index_rest'), {
      credentials: 'same-origin',
    });
    const backendEnrichedEntities = await response.json();

    const items = hidrateAll<EnrichedEntity>(this.hidrator)(backendEnrichedEntities.items);

    return {
      items,
      total: backendEnrichedEntities.total,
    };
  }
}

export default new EnrichedEntityFetcherImplementation(hidrator);
