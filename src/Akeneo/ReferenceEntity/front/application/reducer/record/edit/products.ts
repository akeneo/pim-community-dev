import {NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import {NormalizedCode as NormalizedAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';

export interface ProductsState {
  selectedAttribute: NormalizedAttributeCode | null;
  attributes: NormalizedAttribute[];
  products: NormalizedProduct[];
  totalCount: number;
}

const initProductsState = (): ProductsState => ({
  selectedAttribute: null,
  attributes: [],
  products: [],
  totalCount: 0,
});

export default (
  state: ProductsState = initProductsState(),
  action: {type: string; attributeCode: string; attributes: any[]; products: NormalizedProduct[]; totalCount: number}
) => {
  switch (action.type) {
    case 'PRODUCT_LIST_ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;

    case 'PRODUCT_LIST_PRODUCT_LIST_UPDATED':
      state = {
        ...state,
        products: action.products,
        totalCount: action.totalCount,
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
