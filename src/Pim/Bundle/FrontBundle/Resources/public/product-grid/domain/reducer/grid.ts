import { Display } from 'pimfront/product-grid/domain/event/display';

export interface ProductGridState {
  display: Display;
};

export default (
  state: ProductGridState|undefined,
  action: {
    type: string,
    display: Display
  }): ProductGridState => {
  if (undefined === state) {
    state = {
      display: Display.List
    };
  }

  switch (action.type) {
    case 'CHANGE_GRID_DISPLAY':
      state = {...state, display: action.display};
    break;
    default:
    break;
  }

  return state;
};
