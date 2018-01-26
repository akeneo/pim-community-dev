import GridState from 'pimfront/grid/domain/model/state';
import user, { UserState } from 'pimfront/app/domain/reducer/user';
import structure, { StructureState } from 'pimfront/app/domain/reducer/structure';
import productGrid, { ProductGridState } from 'pimfront/product-grid/domain/reducer/grid';
import ProductInterface, { ProductModel } from 'pimfront/product/domain/model/product';
import { combineReducers } from 'redux';
import grid from 'pimfront/grid/domain/reducer/grid';

export interface State<GridElement> {
  user: UserState;
  grid: GridState<GridElement>;
  productGrid: ProductGridState;
  structure: StructureState;
};

const productMainGrid = (
  state: GridState<ProductInterface>,
  action: {
    type: string,
    identifier: string,
    children: ProductInterface[]
  }
) => {
  switch (action.type) {
    case 'CHILDREN_RECEIVED':
      const items = state.items.map((item: ProductInterface) => {
        if (item.getIdentifier() === action.identifier &&
          item instanceof ProductModel
        ) {
          return ProductModel.create({...item, children: action.children});
        }

        return item;
      });

      state = {...state, items: items};
    break;
    default:
    break;
  }

  return state;
};

export default (state: State<ProductInterface>, action: any): State<ProductInterface> => {
  return combineReducers({
    user,
    grid: (state: GridState<ProductInterface>, action: any) => {
      const gridState = <GridState<ProductInterface>>grid(state, action);

      return productMainGrid(gridState, action)
    },
    productGrid,
    structure
  })(state, action) as State<ProductInterface>;
};
