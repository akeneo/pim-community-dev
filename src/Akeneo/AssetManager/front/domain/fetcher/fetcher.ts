interface Filter {
  field: string;
  operator: string;
  value: any;
  context?: any;
}

export interface Query {
  locale: string;
  size: number;
  channel: string;
  page: number;
  filters: Filter[];
}

export interface SearchResult<Entity> {
  items: Entity[];
  matchesCount: number;
  totalCount: number;
}

export interface SearchFetcher<Entity> {
  search: (query: Query) => Promise<SearchResult<Entity>>;
}

export default interface Fetcher<EntityIdentifier, Entity> extends SearchFetcher<Entity> {
  fetch: (identifier: EntityIdentifier) => Promise<Entity>;
  fetchAll: () => Promise<Entity[]>;
}
