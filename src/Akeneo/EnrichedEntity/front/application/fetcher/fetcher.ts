import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';

interface QueryFilter {
  field: string;
  operator: string;
  value: any;
  context: any;
}

export interface Query {
  locale: string;
  limit: number;
  page: number;
  filters: QueryFilter[];
}

export default interface Fetcher<Entity> {
  fetch: (identifier: string) => Promise<Entity>;
  fetchAll: () => Promise<Entity[]>;
  save: (enrichedEntity: EnrichedEntity) => Promise<EnrichedEntity>;
  search: (query: Query) => Promise<{items: Entity[]; total: number}>;
}
