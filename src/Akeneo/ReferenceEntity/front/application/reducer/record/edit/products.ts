import {NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';

export interface ProductsState {
  selectedAttribute: string | null;
  attributes: any[];
  products: NormalizedProduct[];
}

const initProductsState = (): ProductsState => ({
  selectedAttribute: null,
  attributes: [],
  products: [],
});

export default (
  state: ProductsState = initProductsState(),
  action: {type: string; attributeCode: string; attributes: any[]; products: NormalizedProduct[]}
) => {
  switch (action.type) {
    case 'PRODUCT_LIST_ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;

    case 'PRODUCT_LIST_PRODUCT_LIST_UPDATED':
      state = {
        ...state,
        products: action.products,
      };

      break;

    case 'PRODUCT_LIST_ATTRIBUTE_SELECTED':
      state = {
        ...state,
        selectedAttribute: action.attributeCode,
      };

      break;
    default:
  }

  return state;
};
