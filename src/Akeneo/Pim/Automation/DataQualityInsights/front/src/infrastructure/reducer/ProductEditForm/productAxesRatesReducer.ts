import {Action, ActionCreator, Reducer} from 'redux';
import ProductAxesRates from '../../../domain/ProductAxesRates.interface';

export interface ProductAxesRatesState {
  [productId: string]: ProductAxesRates;
}

interface ProductAxesRatesAction extends Action {
  payload: {
    productId: number;
    axesRates: ProductAxesRates;
  };
}

const GET_PRODUCT_AXES_RATES = 'GET_PRODUCT_AXES_RATES';
export const getProductAxesRatesAction: ActionCreator<ProductAxesRatesAction> = (
  productId: number,
  axesRates: ProductAxesRates
) => {
  return {
    type: GET_PRODUCT_AXES_RATES,
    payload: {
      productId: productId,
      axesRates: axesRates,
    },
  };
};

const initialState: ProductAxesRatesState = {};
const productAxesRatesReducer: Reducer<ProductAxesRatesState, ProductAxesRatesAction> = (
  previousState = initialState,
  {type, payload}
) => {
  switch (type) {
    case GET_PRODUCT_AXES_RATES: {
      return {
        ...previousState,
        [payload.productId]: {
          ...payload.axesRates,
        },
      };
    }
    default:
      return previousState;
  }
};

export default productAxesRatesReducer;
