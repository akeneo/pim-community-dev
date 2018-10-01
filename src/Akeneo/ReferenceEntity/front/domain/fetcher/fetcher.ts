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

export interface SearchFetcher<Entity> {
  search: (query: Query) => Promise<{items: Entity[]; total: number}>;
}

export default interface Fetcher<EntityIdentifier, Entity> extends SearchFetcher<Entity> {
  fetch: (identifier: EntityIdentifier) => Promise<Entity>;
  fetchAll: () => Promise<Entity[]>;
}
