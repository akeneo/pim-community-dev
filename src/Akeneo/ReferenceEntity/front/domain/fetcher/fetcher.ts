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

export interface SearchFetcher<Entity> {
  search: (query: Query) => Promise<{items: Entity[]; total: number}>;
}

export default interface Fetcher<EntityIdentifier, Entity> extends SearchFetcher<Entity> {
  fetch: (identifier: EntityIdentifier) => Promise<Entity>;
  fetchAll: () => Promise<Entity[]>;
}
