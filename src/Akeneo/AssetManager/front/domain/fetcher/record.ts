import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {RecordResult} from 'akeneoreferenceentity/infrastructure/fetcher/record';

export default interface Fetcher {
  fetch: (referenceEntityIdentifier: ReferenceEntityIdentifier, identifier: RecordCode) => Promise<RecordResult>;
  search: (query: Query) => Promise<{items: NormalizedRecord[]; matchesCount: number}>;
}
