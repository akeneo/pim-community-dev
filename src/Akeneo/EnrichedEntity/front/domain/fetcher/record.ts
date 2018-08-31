import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import RecordIdentifier from 'akeneoenrichedentity/domain/model/record/identifier';

export default interface Fetcher {
  fetch: (enrichedEntityIdentifier: EnrichedEntityIdentifier, identifier: RecordIdentifier) => Promise<Record>;
  fetchAll: (enrichedEntityIdentifier: EnrichedEntityIdentifier) => Promise<Record[]>;
  search: (query: Query) => Promise<{items: Record[]; total: number}>;
}
