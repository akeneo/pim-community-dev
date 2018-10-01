import RecordFetcher from 'akeneoreferenceentity/domain/fetcher/record';
import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import hydrator from 'akeneoreferenceentity/application/hydrator/record';
import hydrateAll from 'akeneoreferenceentity/application/hydrator/hydrator';
import {getJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';

const routing = require('routing');

export class RecordFetcherImplementation implements RecordFetcher {
  constructor(private hydrator: (backendRecord: any) => Record) {
    Object.freeze(this);
  }

  async fetch(referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode): Promise<Record> {
    const backendRecord = await getJSON(
      routing.generate('akeneo_reference_entities_records_get_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        recordCode: recordCode.stringValue(),
      })
    ).catch(errorHandler);

    const image = undefined === backendRecord.image ? null : backendRecord.image;

    return this.hydrator({
      ...backendRecord,
      image,
    });
  }

  async fetchAll(referenceEntityIdentifier: ReferenceEntityIdentifier): Promise<Record[]> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_reference_entities_record_index_rest', {referenceEntityIdentifier})
    ).catch(errorHandler);

    return hydrateAll<Record>(this.hydrator)(backendRecords.items);
  }

  async search(query: Query): Promise<{items: Record[]; total: number}> {
    const backendRecords = await getJSON(
      routing.generate('akeneo_reference_entities_record_index_rest', {
        // This is temporary, as soon as we will have a QB in backend it will be way simpler
        referenceEntityIdentifier: query.filters[0].value,
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
