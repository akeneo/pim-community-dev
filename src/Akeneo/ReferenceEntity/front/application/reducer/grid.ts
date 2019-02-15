import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';

export class InvalidArgument extends Error {}

export interface Filter {
  field: string;
  operator: string;
  value: any;
  context: any;
}

export enum Sort {
  Ascending,
  Descending,
}

export interface Column {
  key: string;
  labels: {[locale: string]: string};
  type: string;
  channel: string;
  locale: string;
  code: string;
  attribute: NormalizedAttribute;
}

export interface Query {
  readonly columns: Column[];
  readonly filters: Filter[];
  readonly page: number;
  readonly size: number;
}

class ConcreteQuery implements Query {
  readonly columns: Column[];
  readonly filters: Filter[];
  readonly page: number;
  readonly size: number;

  public constructor(columns: Column[] = [], filters: Filter[] = [], page: number = 0, size: number = 200) {
    this.columns = columns;
    this.filters = filters;
    this.page = page;
    this.size = size;
  }
}

export const createQuery = (rawState: any): Query => {
  return new ConcreteQuery(rawState.columns, rawState.filters, rawState.page, rawState.size);
};

export interface GridState<Element> {
  readonly query: Query;
  readonly items: Element[];
  readonly matchesCount: number;
  readonly totalCount: number;
  readonly isFetching: boolean;
}

class ConcreteGridState<Element> implements GridState<Element> {
  public constructor(
    readonly query: Query,
    readonly items: Element[] = [],
    readonly matchesCount: number = 0,
    readonly totalCount: number = 0,
    readonly isFetching: boolean = false
  ) {}
}

export const createState = <Element>(rawState: any): GridState<Element> => {
  return new ConcreteGridState(
    rawState.query
      ? rawState.query
      : createQuery({
          filters: [],
        }),
    rawState.items,
    rawState.matchesCount,
    rawState.totalCount,
    rawState.isFetching
  );
};

export default <Element>(
  state: GridState<Element> | undefined,
  action: {
    type: string;
    append: boolean;
    matchesCount: number;
    totalCount: number;
    field: string;
    operator: string;
    value: string;
    columns: Column[];
    data: {
      items: Element[];
    };
  }
): GridState<Element> => {
  if (undefined === state) {
    state = createState<Element>({});
  }

  switch (action.type) {
    case 'GRID_TOTAL_COUNT_UPDATED':
      state = {
        ...state,
        totalCount: action.totalCount,
      };
      break;
    case 'GRID_DATA_RECEIVED':
      state = action.append
        ? {
            ...state,
            items: [...state.items, ...action.data.items],
            matchesCount: action.matchesCount,
            totalCount: action.totalCount,
          }
        : {...state, items: action.data.items, matchesCount: action.matchesCount, totalCount: action.totalCount};
      break;
    case 'GRID_START_LOADING_RESULTS':
      state = {...state, isFetching: true};
      break;
    case 'GRID_STOP_LOADING_RESULTS':
      state = {...state, isFetching: false};
      break;
    case 'GRID_GO_NEXT_PAGE':
      state = {...state, query: {...state.query, page: state.query.page + 1}};
      break;
    case 'GRID_GO_FIRST_PAGE':
      state = {...state, query: {...state.query, page: 0}};
      break;
    case 'GRID_UPDATE_COLUMNS':
      state = {...state, query: {...state.query, columns: action.columns}};
      break;
    case 'GRID_UPDATE_FILTER':
      const filters = state.query.filters.filter((filter: Filter) => filter.field !== action.field);
      const filter = {field: action.field, operator: action.operator, value: action.value, context: {}};

      state = {...state, query: {...state.query, filters: [...filters, filter]}};
      break;
    case 'GRID_REMOVE_FILTER':
      const filtersUpdated = state.query.filters.filter((filter: Filter) => filter.field !== action.field);

      state = {...state, query: {...state.query, filters: filtersUpdated}};
      break;
    default:
      break;
  }

  return state;
};
