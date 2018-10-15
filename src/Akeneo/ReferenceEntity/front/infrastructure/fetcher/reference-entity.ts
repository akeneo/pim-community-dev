import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import hydrator from 'akeneoreferenceentity/application/hydrator/reference-entity';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export interface ReferenceEntityFetcher {
  fetch: (identifier: ReferenceEntityIdentifier) => Promise<ReferenceEntityResult>;
  fetchAll: () => Promise<ReferenceEntity[]>;
  search: (query: Query) => Promise<{items: ReferenceEntity[]; total: number}>;
}

export type ReferenceEntityResult = {
  referenceEntity: ReferenceEntity;
  recordCount: number;
};

export class ReferenceEntityFetcherImplementation implements ReferenceEntityFetcher {
  constructor(private hydrator: (backendReferenceEntity: any) => ReferenceEntity) {
    Object.freeze(this);
  }

  async fetch(identifier: ReferenceEntityIdentifier): Promise<ReferenceEntityResult> {
    const backendReferenceEntity = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_get_rest', {identifier: identifier.stringValue()})
    ).catch(errorHandler);

    return {referenceEntity: this.hydrator(backendReferenceEntity), recordCount: backendReferenceEntity.record_count};
  }

  async fetchAll(): Promise<ReferenceEntity[]> {
    const backendReferenceEntities = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_index_rest')
    ).catch(errorHandler);

    return hydrateAll<ReferenceEntity>(this.hydrator)(backendReferenceEntities.items);
  }

  async search(): Promise<{items: ReferenceEntity[]; total: number}> {
    const backendReferenceEntities = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_index_rest')
    ).catch(errorHandler);

    const items = hydrateAll<ReferenceEntity>(this.hydrator)(backendReferenceEntities.items);

    return {
      items,
      total: backendReferenceEntities.total,
    };
  }
}

export default new ReferenceEntityFetcherImplementation(hydrator);
