import {NormalizedProduct} from 'akeneoreferenceentity/domain/model/product/product';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import {NormalizedCode as NormalizedAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';

export interface ProductsState {
  selectedAttribute: NormalizedAttribute | null;
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

const getAttribute = (state: ProductsState, attributeCode: NormalizedAttributeCode): NormalizedAttribute | undefined => {
  return state.attributes.find((attribute: any) => attributeCode === attribute.code);
};

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
      const attribute = getAttribute(state, action.attributeCode);
      const selectedAttribute = undefined !== attribute ? attribute : null;
      state = {
        ...state,
        selectedAttribute,
      };

      break;
    default:
  }

  return state;
};
