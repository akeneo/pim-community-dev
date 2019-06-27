import {Query} from 'akeneoreferenceentity/domain/fetcher/fetcher';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {RecordResult} from 'akeneoreferenceentity/infrastructure/fetcher/record';

export default interface Fetcher {
  fetch: (referenceEntityIdentifier: ReferenceEntityIdentifier, identifier: RecordCode) => Promise<RecordResult>;
  search: (query: Query) => Promise<{items: NormalizedItemRecord[]; matchesCount: number}>;
}
