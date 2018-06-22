import {Query} from 'akeneoenrichedentity/domain/fetcher/fetcher';
import Record from 'akeneoenrichedentity/domain/model/record/record';

export default interface Fetcher {
  fetch: (identifier: string, enrichedEntityIdentifier: string) => Promise<Record>;
  fetchAll: (enrichedEntityIdentifier: string) => Promise<Record[]>;
  search: (query: Query) => Promise<{items: Record[]; total: number}>;
}
