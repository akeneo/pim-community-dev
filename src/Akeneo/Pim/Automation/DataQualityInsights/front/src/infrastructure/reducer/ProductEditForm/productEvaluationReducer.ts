import {Action, ActionCreator, Reducer} from 'redux';
import {AxesCollection, ProductEvaluation} from '../../../domain';

export interface ProductEvaluationState {
  [productId: string]: ProductEvaluation;
}

interface ProductEvaluationAction extends Action {
  payload: {
    productId: number;
    evaluation?: ProductEvaluation;
    axisRates?: AxesCollection;
  };
}

const GET_PRODUCT_EVALUATION = 'GET_PRODUCT_EVALUATION';
export const getProductEvaluationAction: ActionCreator<ProductEvaluationAction> = (
  productId: number,
  evaluation: ProductEvaluation
) => {
  return {
    type: GET_PRODUCT_EVALUATION,
    payload: {
      productId: productId,
      evaluation: evaluation,
    },
  };
};

const initialState: ProductEvaluationState = {};
const productEvaluationReducer: Reducer<ProductEvaluationState, ProductEvaluationAction> = (
  previousState = initialState,
  {type, payload}
) => {
  switch (type) {
    case GET_PRODUCT_EVALUATION: {
      const {productId, evaluation} = payload;
      return {
        ...previousState,
        [productId]: evaluation,
      };
    }
    default:
      return previousState;
  }
};

export default productEvaluationReducer;
