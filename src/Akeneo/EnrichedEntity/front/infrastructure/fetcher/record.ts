import RecordFetcher from 'akeneoenrichedentity/domain/fetcher/record';
import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import hydrator from 'akeneoenrichedentity/application/hydrator/record';
import hydrateAll from 'akeneoenrichedentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoenrichedentity/tools/fetch';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';
import errorHandler from 'akeneoenrichedentity/infrastructure/tools/error-handler';

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
    ).catch(errorHandler);

    const image = undefined === backendRecord.image ? null : backendRecord.image;

    return this.hydrator({
      ...backendRecord,
      image,
    });
  }

  async fetchAll(enrichedEntityIdentifier: EnrichedEntityIdentifier): Promise<Record[]> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {enrichedEntityIdentifier})
    ).catch(errorHandler);

    return hydrateAll<Record>(this.hydrator)(backendRecords.items);
  }

  async search(query: Query): Promise<{items: Record[]; total: number}> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_enriched_entities_record_index_rest', {
        // This is temporary, as soon as we will have a QB in backend it will be way simpler
        enrichedEntityIdentifier: query.filters[0].value,
      })
    ).catch(errorHandler);
    const items = hydrateAll<Record>(this.hydrator)(
      backendRecords.items.map((backendRecord: any) => {
        // This is temporary: the backend should send the image and the values
        return {...backendRecord, image: undefined === backendRecord.image ? null : backendRecord.image, values: []};
      })
    );

    return {
      items,
      total: backendRecords.total,
    };
  }
}

export default new RecordFetcherImplementation(hydrator);
