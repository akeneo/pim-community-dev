import {useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {isEmpty} from "lodash";

import {fetchProductAxisRates} from '../fetcher';
import {getProductAxesRatesAction} from "../reducer";
import useProductAxesRates from "./useProductAxesRates";

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

const useFetchProductAxisRates = () => {
  const [hasToBeEvaluated, setHasToBeEvaluated] = useState<boolean>(false);
  const [retries, setRetries] = useState<number>(0);
  const {axesRates, productId, productUpdated} = useProductAxesRates();

  const dispatchAction = useDispatch();

  useEffect(() => {
    if (productId && hasToBeEvaluated && retries < MAXIMUM_RETRIES) {
      setTimeout(() => {
        (async () => {
          const data = await fetchProductAxisRates(productId);
          dispatchAction(getProductAxesRatesAction(productId, data));
        })();
      }, getRetryDelay(retries));
    }
  }, [hasToBeEvaluated, retries]);

  useEffect(() => {
    if(Object.keys(axesRates).length === 0) {
      return;
    }
    const notEvaluatedAxesList = Object.values(axesRates).filter((axisEvaluation) => {
      return isEmpty(axisEvaluation.rates);
    });

    if (notEvaluatedAxesList.length === 0) {
      setRetries(0);
      setHasToBeEvaluated(false);
    }
    else {
      setRetries(retries + 1);
      setHasToBeEvaluated(true);
    }
  }, [axesRates]);

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

  return axesRates;
};

export default useFetchProductAxisRates;
