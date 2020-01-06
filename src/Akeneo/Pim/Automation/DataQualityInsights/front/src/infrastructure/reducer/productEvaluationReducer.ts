import {Action, Reducer} from "redux";
import {ProductEvaluation} from "../../domain/Evaluation.interface";

export interface ProductEvaluationState {
  [productId: string]: ProductEvaluation;
}

interface ProductEvaluationAction extends Action {
  payload: {
    productId: number;
    evaluation: ProductEvaluation;
  };
}

const GET_PRODUCT_EVALUATION = 'GET_PRODUCT_EVALUATION';

export const getProductEvaluationAction = (productId:number, evaluation: ProductEvaluation): ProductEvaluationAction => {
  return {
    type: GET_PRODUCT_EVALUATION,
    payload: {
      productId: productId,
      evaluation: evaluation,
    }
  }
};

const initialState: ProductEvaluationState = {};

const productEvaluationReducer: Reducer<ProductEvaluationState, ProductEvaluationAction> = (previousState = initialState, {type, payload}) => {
  switch (type) {
    case GET_PRODUCT_EVALUATION:
      return {
        ...previousState,
        [payload.productId]: payload.evaluation,
      };
    default:
      return previousState;
  }
};

export default productEvaluationReducer;
