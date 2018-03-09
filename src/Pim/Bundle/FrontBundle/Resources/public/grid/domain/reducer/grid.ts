import GridState, {createState as createGridState} from 'pimfront/grid/domain/model/state';
import {Column} from 'pimfront/grid/domain/model/query';
import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';

export default <Element>(
  state: GridState<Element> | undefined,
  action: {
    type: string;
    append: boolean;
    total: number;
    filter: NormalizedFilter;
    data: {
      items: Element[];
      columns: Column[];
    };
  }
): GridState<Element> => {
  if (undefined === state) {
    state = createGridState<Element>({});
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
    case 'COLUMNS_UPDATED':
      state = {...state, query: {...state.query, columns: action.data.columns}};
      break;
    case 'FILTER_ADDED':
      const newFilters: NormalizedFilter[] = Array.from(new Set([...state.query.filters, action.filter]));

      state = {...state, query: {...state.query, filters: newFilters}};
      break;
    default:
      break;
  }

  return state;
};
