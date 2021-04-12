import {Selection} from 'akeneo-design-system';

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

export const createRecordSelectionQuery = (
  referenceEntityIdentifier: string,
  selection: Selection,
  filters: Filter[],
  searchValue: string,
  channel: string,
  locale: string
): Query => ({
  locale,
  channel,
  size: 50,
  page: 0,
  filters: [
    ...filters,
    {
      field: 'reference_entity',
      operator: '=',
      value: referenceEntityIdentifier,
      context: {},
    },
    {
      field: 'full_text',
      operator: '=',
      value: searchValue,
      context: {},
    },
    {
      field: 'code',
      operator: selection.mode === 'in' ? 'IN' : 'NOT IN',
      value: selection.collection,
      context: {},
    },
  ],
});

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
