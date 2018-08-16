import RecordFetcher from 'akeneoenrichedentity/domain/fetcher/record';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import hydrator from 'akeneoenrichedentity/application/hydrator/record';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';

const routing = require('routing');

export class RecordFetcherImplementation implements RecordFetcher {
  constructor(private hydrator: (backendRecord: any) => Record) {
    Object.freeze(this);
  }

  async fetch(identifier: RecordIdentifier, enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Record> {
    const backendRecord = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {enrichedEntityIdentifier, identifier})
    );

    return this.hydrator(backendRecord);
  }

  async fetchAll(enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Record[]> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {enrichedEntityIdentifier})
    );

    return hydrateAll<Record>(this.hydrator)(backendRecords);
  }

  async search(query: Query): Promise<{items: Record[]; total: number}> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {
        // This is temporary, as soon as we will have a QB in backend it will be way simpler
        enrichedEntityIdentifier: query.filters[0].value,
      })
    );

    const items = hydrateAll<Record>(this.hydrator)(backendRecords.items);

    return {
      items,
      total: backendRecords.total,
    };
  }
}

export default new RecordFetcherImplementation(hydrator);
