import RecordFetcher from 'akeneoenrichedentity/domain/fetcher/record';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import hydrator from 'akeneoenrichedentity/application/hydrator/record';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';
import attributeFetcher from 'akeneoenrichedentity/infrastructure/fetcher/attribute';

const routing = require('routing');

export class RecordFetcherImplementation implements RecordFetcher {
  constructor(private hydrator: (backendRecord: any) => Record) {
    Object.freeze(this);
  }

  async fetch(enrichedEntityIdentifier: EnrichedEntityIdentifier, recordCode: RecordCode): Promise<Record> {
    const backendRecord = await getJSON(
      routing.generate('akeneo_enriched_entities_records_get_rest', {
        enrichedEntityIdentifier: enrichedEntityIdentifier.stringValue(),
        recordCode: recordCode.stringValue(),
      })
    );

    const attributes = await attributeFetcher.fetchAll(enrichedEntityIdentifier);

    const record = {
      ...backendRecord,
      image: {
        filePath: '4/b/2/3/4b23afc720c1698357eb6dce11b0e2a85af7b1be_tom_dixon.jpeg',
        originalFilename: 'tom_dixon.jpeg',
      },
      values: {
        description_1234567890: [
          {
            identifier: 'description_1234567890',
            data: 'a nice description',
            locale: 'en_US',
            channel: null,
          },
        ],
      },
      attributes,
    };

    return this.hydrator(record);
  }

  async fetchAll(enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Record[]> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {enrichedEntityIdentifier})
    );

    return hydrateAll<Record>(this.hydrator)(backendRecords.items);
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
