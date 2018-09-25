import Fetcher from 'akeneoenrichedentity/domain/fetcher/fetcher';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import hydrator from 'akeneoenrichedentity/application/hydrator/enriched-entity';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/identifier';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

const routing = require('routing');

export interface EnrichedEntityFetcher extends Fetcher<EnrichedEntityIdentifier, EnrichedEntity> {}

export class EnrichedEntityFetcherImplementation implements EnrichedEntityFetcher {
  constructor(private hydrator: (backendEnrichedEntity: any) => EnrichedEntity) {
    Object.freeze(this);
  }

  async fetch(identifier: EnrichedEntityIdentifier): Promise<EnrichedEntity> {
    const backendEnrichedEntity = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entity_get_rest', {identifier: identifier.stringValue()})
    ).catch(errorHandler);

    return this.hydrator(backendEnrichedEntity);
  }

  async fetchAll(): Promise<EnrichedEntity[]> {
    const backendEnrichedEntities = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entity_index_rest')
    ).catch(errorHandler);

    return hydrateAll<EnrichedEntity>(this.hydrator)(backendEnrichedEntities.items);
  }

  async search(): Promise<{items: EnrichedEntity[]; total: number}> {
    const backendEnrichedEntities = await getJSON(
      routing.generate('akeneo_enriched_entities_enriched_entity_index_rest')
    ).catch(errorHandler);

    const items = hydrateAll<EnrichedEntity>(this.hydrator)(backendEnrichedEntities.items);

    return {
      items,
      total: backendEnrichedEntities.total,
    };
  }
}

export default new EnrichedEntityFetcherImplementation(hydrator);
