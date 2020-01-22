import {Action, ActionCreator, Reducer} from "redux";
import {isEmpty} from "lodash";
import {AxesCollection, ProductEvaluation} from "../../domain";

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
export const getProductEvaluationAction: ActionCreator<ProductEvaluationAction> = (productId:number, evaluation: ProductEvaluation) => {
  return {
    type: GET_PRODUCT_EVALUATION,
    payload: {
      productId: productId,
      evaluation: evaluation,
    }
  }
};

const GET_PRODUCT_EVALUATION_RATES = 'GET_PRODUCT_EVALUATION_RATES';
export const getProductEvaluationRatesAction: ActionCreator<ProductEvaluationAction> = (productId: number, axisRates: AxesCollection) => {
  return {
    type: GET_PRODUCT_EVALUATION_RATES,
    payload: {
      productId: productId,
      axisRates: axisRates
    }
  }
};


const initialState: ProductEvaluationState = {};
const productEvaluationReducer: Reducer<ProductEvaluationState, ProductEvaluationAction> = (previousState = initialState, {type, payload}) => {
  switch (type) {
    case GET_PRODUCT_EVALUATION: {
      const {productId, evaluation} = payload;
      return {
        ...previousState,
        [productId]: evaluation,
      };
    }
    case GET_PRODUCT_EVALUATION_RATES: {
      const {productId, axisRates} = payload;
      const evaluation = {
        ...previousState[productId],
      };

      if (axisRates) {
        Object.entries(axisRates).forEach(([axisName, axisData]) => {
          const initialAxisData = isEmpty(axisData.rates) ?  {} : evaluation[axisName];
          evaluation[axisName] = {
            ...initialAxisData,
          };

          Object.entries(axisData.rates).forEach(([channel, channelData]) => {
            evaluation[axisName][channel] = {
              ...evaluation[axisName][channel],
            };

            Object.entries(channelData).forEach(([locale, rateRank]) => {
              evaluation[axisName][channel][locale] = {
                ...evaluation[axisName][channel][locale],
                rate: rateRank
              };
            });
          });
        });
      }

      return {
        ...previousState,
        [productId]: {
          ...evaluation
        },
      };
    }
    default:
      return previousState;
  }
};

export default productEvaluationReducer;
