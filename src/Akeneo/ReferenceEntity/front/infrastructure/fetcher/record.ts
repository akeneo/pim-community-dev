import RecordFetcher from 'akeneoreferenceentity/domain/fetcher/record';
import {Query, SearchResult} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import hydrator from 'akeneoreferenceentity/application/hydrator/record';
import {getJSON, putJSON} from 'akeneoreferenceentity/tools/fetch';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import errorHandler from 'akeneoreferenceentity/infrastructure/tools/error-handler';
import {Filter} from 'akeneoreferenceentity/application/reducer/grid';
import {ReferenceEntityPermission} from 'akeneoreferenceentity/domain/model/permission/reference-entity';
const routing = require('routing');

class InvalidArgument extends Error {}

export type RecordResult = {
  record: Record;
  permission: ReferenceEntityPermission;
};

export class RecordFetcherImplementation implements RecordFetcher {
  async fetch(referenceEntityIdentifier: ReferenceEntityIdentifier, recordCode: RecordCode): Promise<RecordResult> {
    const backendRecord = await getJSON(
      routing.generate('akeneo_reference_entities_record_get_rest', {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        recordCode: recordCode.stringValue(),
      })
    ).catch(errorHandler);

    return {
      record: hydrator(backendRecord),
      permission: {
        referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        edit: backendRecord.permission.edit,
      },
    };
  }

  async search(query: Query): Promise<SearchResult<NormalizedRecord>> {
    const referenceEntityCode = query.filters.find((filter: Filter) => 'reference_entity' === filter.field);
    if (undefined === referenceEntityCode) {
      throw new InvalidArgument('The search repository expects a reference_entity filter');
    }

    const backendRecords = await putJSON(
      routing.generate('akeneo_reference_entities_record_index_rest', {
        referenceEntityIdentifier: referenceEntityCode.value,
      }),
      query
    ).catch(errorHandler);

    return {
      items: backendRecords.items,
      matchesCount: backendRecords.matches_count,
      totalCount: backendRecords.total_count,
    };
  }
}

export default new RecordFetcherImplementation();
