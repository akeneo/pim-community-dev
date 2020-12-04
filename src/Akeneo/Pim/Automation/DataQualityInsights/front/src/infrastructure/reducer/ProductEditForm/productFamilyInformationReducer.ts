import {Action, Reducer} from 'redux';
import {Family as FamilyInformation} from '@akeneo-pim-community/data-quality-insights/src/domain';

export interface ProductFamilyInformationState {
  [family: string]: FamilyInformation;
}

interface ProductFamilyInformationAction extends Action {
  payload: {
    family: FamilyInformation;
  };
}

const GET_PRODUCT_FAMILY_INFORMATION = 'GET_PRODUCT_FAMILY_INFORMATION';

export const getProductFamilyInformationAction = (family: FamilyInformation): ProductFamilyInformationAction => {
  return {
    type: GET_PRODUCT_FAMILY_INFORMATION,
    payload: {
      family: family,
    },
  };
};

const initialState: ProductFamilyInformationState = {};

const productFamilyInformationReducer: Reducer<ProductFamilyInformationState, ProductFamilyInformationAction> = (
  previousState = initialState,
  {type, payload}
) => {
  switch (type) {
    case GET_PRODUCT_FAMILY_INFORMATION:
      return {
        ...previousState,
        [payload.family.code]: payload.family,
      };
    default:
      return previousState;
  }
};

export default productFamilyInformationReducer;
