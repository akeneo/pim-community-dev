import {Action, Reducer} from 'redux';
import {Axis} from "../../domain";

interface AxisRatesResponse {
  [axis: string]: Axis;
}

interface UpdateAxisRatesAction extends Action {
  payload: {
    productId: number,
    axisRates: AxisRatesResponse
  };
}

export interface AxisRatesState {
  [productId: string]: {
    [axis: string]: Axis;
  };
}

const GET_PRODUCT_AXIS_RATES = 'GET_PRODUCT_AXIS_RATES';

export const getProductAxisRatesAction = (productId: number, axisRates: AxisRatesResponse): UpdateAxisRatesAction => {
  return {
    type: GET_PRODUCT_AXIS_RATES,
    payload: {
      productId: productId,
      axisRates: axisRates
    }
  }
};

const productAxisRatesReducer: Reducer<AxisRatesState, UpdateAxisRatesAction> = (previousState = {}, {type, payload}) => {
  switch (type) {
    case GET_PRODUCT_AXIS_RATES:
      return {
        ...previousState,
        [payload.productId]: payload.axisRates,
      };
    default:
      return previousState;
  }
};

export default productAxisRatesReducer;
