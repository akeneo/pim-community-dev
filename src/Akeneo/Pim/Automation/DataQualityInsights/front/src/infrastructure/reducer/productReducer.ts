import {Action, ActionCreator, Reducer} from "redux";
import {Product} from '../../domain';

export interface ProductState extends Product {

}

interface ProductAction extends Action {
  payload: {
    product: Product;
  };
}

const INITIALIZE_PRODUCT = 'INITIALIZE_PRODUCT';
export const initializeProductAction: ActionCreator<ProductAction> = (product: Product) => {
  return {
    type: INITIALIZE_PRODUCT,
    payload: {
      product: product,
    }
  }
};

const initialState: ProductState = {
  categories: [],
  enabled: false,
  family: null,
  identifier: null,
  created: null,
  updated: null,
  meta: {
    id: null,
    label: {},
  },
};

const productReducer: Reducer<ProductState, ProductAction> = (previousState = initialState, {type, payload}) => {
  switch (type) {
    case INITIALIZE_PRODUCT:
      return {
        ...payload.product
      };
    default:
      return previousState;
  }
};

export default productReducer;
