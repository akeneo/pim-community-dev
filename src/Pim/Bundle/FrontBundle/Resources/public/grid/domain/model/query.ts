import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';

enum Sort {
  Ascending,
  Descending,
}

export interface Column {
  sortable: boolean;
  sort?: Sort;
  action: boolean;
  label: string;
}

export default interface Query {
  readonly columns: Column[];
  readonly filters: NormalizedFilter[];
  readonly page: number;
  readonly limit: number;
};

class ConcreteQuery implements Query {
  readonly columns: Column[];
  readonly filters: NormalizedFilter[];
  readonly page: number;
  readonly limit: number;

  public constructor(columns: Column[] = [], filters: NormalizedFilter[] = [], page: number = 0, limit: number = 25) {
    this.columns = columns;
    this.filters = filters;
    this.page = page;
    this.limit = limit;
  }
}

export const createQuery = (rawState: any): Query => {
  return new ConcreteQuery(rawState.columns, rawState.filters, rawState.page, rawState.limit);
};
