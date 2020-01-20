import {useEffect, useState} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import {isEmpty} from "lodash";

import {fetchProductDataQualityEvaluation} from '../fetcher';
import {ProductEditFormState} from "../store";
import {getProductEvaluationAction} from "../reducer";

const MAXIMUM_RETRIES = 10;
const RETRY_MILLISECONDS_DELAY = 200;

/**
 * @example
 *  Retry | Delay
 *    0   | 0
 *    1   | 200
 *    2   | 800
 *    3   | 1800
 *    4   | 3200
 *    5   | 5000
 *    6   | 7200
 *    7   | 9600
 *    8   | 12800
 *    9   | 16200
 */
const getRetryDelay = (retry: number) => {
  return Math.pow(retry, 2) * RETRY_MILLISECONDS_DELAY;
};

const useFetchProductDataQualityEvaluation = () => {
  const [hasToBeEvaluated, setHasToBeEvaluated] = useState<boolean>(false);
  const [retries, setRetries] = useState<number>(0);

  const {productId, productUpdated, evaluation} = useSelector((state: ProductEditFormState) => {
    const productId = state.product.meta.id;
    const productUpdated = state.product.updated;
    const evaluation = productId ? state.productEvaluation[productId] : {};

    return {
      evaluation: evaluation || {},
      productId: productId,
      productUpdated
    };
  });

  const dispatchAction = useDispatch();

  useEffect(() => {
    if (productId && hasToBeEvaluated && retries < MAXIMUM_RETRIES) {
      setTimeout(() => {
        (async () => {
          if (!productId) {
            return;
          }
          const data = await fetchProductDataQualityEvaluation(productId);
          dispatchAction(getProductEvaluationAction(productId, data));
        })();
      }, getRetryDelay(retries));
    }
  }, [hasToBeEvaluated, retries]);


  useEffect(() => {
    const notEvaluatedAxesList = Object.values(evaluation).filter((axisEvaluation) => {
      return isEmpty(axisEvaluation);
    });

    if (notEvaluatedAxesList.length === 0) {
      setRetries(0);
      setHasToBeEvaluated(false);
    }
    else {
      setRetries(retries + 1);
      setHasToBeEvaluated(true);
    }
  }, [evaluation]);

  useEffect(() => {
    if (retries >= MAXIMUM_RETRIES) {
      setRetries(0);
      setHasToBeEvaluated(false);
    }
  }, [retries]);

  useEffect(() => {
    if (productId && productUpdated) {
      setHasToBeEvaluated(true);
    }
  }, [productId, productUpdated]);

  return evaluation;
};

export default useFetchProductDataQualityEvaluation;
