import GridState from 'pimfront/grid/domain/model/state';
import { UserState } from 'pimfront/app/domain/reducer/user';
import { StructureState } from 'pimfront/app/domain/reducer/structure';
import productGrid, { ProductGridState } from 'pimfront/product-grid/domain/reducer/grid';
import mainReducer from 'pimfront/grid/application/reducer/reducer';
import ProductInterface, { ProductModel } from 'pimfront/product/domain/model/product';

export interface State<GridElement> {
  user: UserState;
  grid: GridState<GridElement>;
  productGrid: ProductGridState;
  structure: StructureState;
};

const customGridReducer = (
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
  const newState = mainReducer(state, action) as State<ProductInterface>;

  newState.grid = customGridReducer(newState.grid, action);
  newState.productGrid = productGrid(newState.productGrid, action);

  return newState;
};
