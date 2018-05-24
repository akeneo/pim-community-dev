export class InvalidArgument extends Error {}

export class NormalizedFilter {
  private constructor(readonly field: string, readonly operator: string, readonly value: any) {}

  public static create({field, operator, value}: {field: string; operator: string; value: any}) {
    if (undefined === field || undefined === operator || undefined === value) {
      throw new InvalidArgument(`The given normalized filter is not valid. Arguments: ${JSON.stringify(arguments)}`);
    }

    return new NormalizedFilter(field, operator, value);
  }
}

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

export interface Query {
  readonly columns: Column[];
  readonly filters: NormalizedFilter[];
  readonly page: number;
  readonly limit: number;
}

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

export interface GridState<Element> {
  readonly query: Query;
  readonly items: Element[];
  readonly total: number;
  readonly isFetching: boolean;
}

class ConcreteGridState<Element> implements GridState<Element> {
  readonly query: Query;
  readonly items: Element[];
  readonly total: number;
  readonly isFetching: boolean;

  public constructor(query: Query, items: Element[] = [], total: number = 0, isFetching: boolean = false) {
    this.query = query;
    this.items = items;
    this.total = total;
    this.isFetching = isFetching;
  }
}

export const createState = <Element>(rawState: any): GridState<Element> => {
  return new ConcreteGridState(
    rawState.query
      ? rawState.query
      : createQuery({
          filters: [],
        }),
    rawState.items,
    rawState.total,
    rawState.isFetching
  );
};

export default <Element>(
  state: GridState<Element> | undefined,
  action: {
    type: string;
    append: boolean;
    total: number;
    data: {
      items: Element[];
    };
  }
): GridState<Element> => {
  if (undefined === state) {
    state = createState<Element>({});
  }

  switch (action.type) {
    case 'DATA_RECEIVED':
      state = action.append
        ? {...state, items: [...state.items, ...action.data.items], total: action.total}
        : {...state, items: action.data.items, total: action.total};
      break;
    case 'START_LOADING_RESULTS':
      state = {...state, isFetching: true};
      break;
    case 'STOP_LOADING_RESULTS':
      state = {...state, isFetching: false};
      break;
    case 'GO_NEXT_PAGE':
      state = {...state, query: {...state.query, page: state.query.page + 1}};
      break;
    case 'GO_FIRST_PAGE':
      state = {...state, query: {...state.query, page: 0}};
      break;
    default:
      break;
  }

  return state;
};
