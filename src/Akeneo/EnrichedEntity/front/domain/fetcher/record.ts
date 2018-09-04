import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordCode from 'akeneoenrichedentity/domain/model/record/code';

export default interface Fetcher {
  fetch: (enrichedEntityIdentifier: EnrichedEntityIdentifier, identifier: RecordCode) => Promise<Record>;
  fetchAll: (enrichedEntityIdentifier: EnrichedEntityIdentifier) => Promise<Record[]>;
  search: (query: Query) => Promise<{items: Record[]; total: number}>;
}
