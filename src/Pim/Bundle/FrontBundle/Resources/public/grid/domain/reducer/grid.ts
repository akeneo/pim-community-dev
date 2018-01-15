import GridState, { createState as createGridState } from 'pimfront/grid/domain/model/state';
import { Column } from 'pimfront/grid/domain/model/query';

export default <Element>(
  state: GridState<Element>|undefined,
  action: {
    type: string,
    data: {
      items: Element[],
      columns: Column[]
    }
  }): GridState<Element> => {
  if (undefined === state) {
    state = createGridState<Element>({});
  }

  switch (action.type) {
    case 'DATA_RECEIVED':
      state = {...state, items: action.data.items}
    break;
    case 'COLUMNS_UPDATED':
      state = {...state, query: {...state.query, columns: action.data.columns}}
    break;
    default:
    break;
  }

  return state;
};
