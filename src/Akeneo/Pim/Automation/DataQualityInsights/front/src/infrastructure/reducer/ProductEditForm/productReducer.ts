import {Action, ActionCreator, Reducer} from 'redux';
import {Product} from '../../../domain';

export type ProductState = Product;

const INITIALIZE_PRODUCT = 'INITIALIZE_PRODUCT';
const UNSET_PRODUCT = 'UNSET_PRODUCT';

type InitializeProductAction = Action<typeof INITIALIZE_PRODUCT> & {
  payload: {
    product: Product;
  };
};

type UnsetProductAction = Action<typeof UNSET_PRODUCT>;

type ProductAction = InitializeProductAction | UnsetProductAction;

export const initializeProductAction: ActionCreator<InitializeProductAction> = (product: Product) => {
  return {
    type: INITIALIZE_PRODUCT,
    payload: {
      product: product,
    },
  };
};

export const unsetProductAction: ActionCreator<UnsetProductAction> = () => ({type: UNSET_PRODUCT});

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
    attributes_for_this_level: [],
    level: null,
    model_type: 'product',
    variant_navigation: [],
    family_variant: {
      variant_attribute_sets: [{attributes: []}],
    },
    parent_attributes: [],
  },
};

const productReducer: Reducer<ProductState, ProductAction> = (previousState = initialState, action) => {
  switch (action.type) {
    case INITIALIZE_PRODUCT:
      return {
        ...action.payload.product,
      };
    case UNSET_PRODUCT:
      return initialState;
    default:
      return previousState;
  }
};

export default productReducer;
