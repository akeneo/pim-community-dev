import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';

export default interface Fetcher {
  fetch: (referenceEntityIdentifier: ReferenceEntityIdentifier, identifier: RecordCode) => Promise<Record>;
  fetchAll: (referenceEntityIdentifier: ReferenceEntityIdentifier) => Promise<Record[]>;
  search: (query: Query) => Promise<{items: NormalizedRecord[]; matchesCount: number}>;
}
