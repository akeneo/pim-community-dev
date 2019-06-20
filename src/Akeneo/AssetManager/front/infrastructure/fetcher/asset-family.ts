import {Query, SearchResult} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import hydrator from 'akeneoreferenceentity/application/hydrator/reference-entity';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import hydrateAttribute from 'akeneoreferenceentity/application/hydrator/attribute';
import {ReferenceEntityPermission} from 'akeneoreferenceentity/domain/model/permission/reference-entity';
import ReferenceEntityListItem, {
  denormalizeReferenceEntityListItem,
} from 'akeneoreferenceentity/domain/model/reference-entity/list';

const routing = require('routing');

export interface ReferenceEntityFetcher {
  fetch: (identifier: ReferenceEntityIdentifier) => Promise<ReferenceEntityResult>;
  fetchAll: () => Promise<ReferenceEntityListItem[]>;
  search: (query: Query) => Promise<SearchResult<ReferenceEntityListItem>>;
}

export type ReferenceEntityResult = {
  referenceEntity: ReferenceEntity;
  recordCount: number;
  attributes: Attribute[];
  permission: ReferenceEntityPermission;
};

export class ReferenceEntityFetcherImplementation implements ReferenceEntityFetcher {
  async fetch(identifier: ReferenceEntityIdentifier): Promise<ReferenceEntityResult> {
    const backendReferenceEntity = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_get_rest', {identifier: identifier.stringValue()})
    ).catch(errorHandler);

    return {
      referenceEntity: hydrator(backendReferenceEntity),
      recordCount: backendReferenceEntity.record_count,
      attributes: backendReferenceEntity.attributes.map((normalizedAttribute: NormalizedAttribute) =>
        hydrateAttribute(normalizedAttribute)
      ),
      permission: {
        referenceEntityIdentifier: identifier.stringValue(),
        edit: backendReferenceEntity.permission.edit,
      },
    };
  }

  async fetchAll(): Promise<ReferenceEntityListItem[]> {
    const backendReferenceEntities = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_index_rest')
    ).catch(errorHandler);

    return hydrateAll<ReferenceEntityListItem>(denormalizeReferenceEntityListItem)(backendReferenceEntities.items);
  }

  async search(): Promise<SearchResult<ReferenceEntityListItem>> {
    const backendReferenceEntities = await getJSON(
      routing.generate('akeneo_reference_entities_reference_entity_index_rest')
    ).catch(errorHandler);

    const items = hydrateAll<ReferenceEntityListItem>(denormalizeReferenceEntityListItem)(
      backendReferenceEntities.items
    );

    return {
      items,
      matchesCount: backendReferenceEntities.matchesCount,
      totalCount: backendReferenceEntities.matchesCount,
    };
  }
}

export default new ReferenceEntityFetcherImplementation();
