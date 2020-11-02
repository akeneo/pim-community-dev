import {useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {isEmpty} from 'lodash';

import {fetchProductAxisRates} from '../../fetcher';
import {getProductAxesRatesAction} from '../../reducer';
import useProductAxesRates from './useProductAxesRates';

const MAXIMUM_RETRIES = 10;
const RETRY_MILLISECONDS_DELAY = 500;

/**
 * @example
 *  Retry | Delay
 *    0   | 0
 *    1   | 500
 *    2   | 2000
 *    3   | 4500
 *    4   | 8000
 *    5   | 12500
 *    6   | 18000
 *    7   | 24500
 *    8   | 32000
 *    9   | 40500
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
    if (axesRates !== undefined && !isEmpty(axesRates)) {
      const notEvaluatedAxesList = Object.values(axesRates).filter(axisRates => {
        return isEmpty(axisRates.rates);
      });

      if (notEvaluatedAxesList.length === 0) {
        setRetries(0);
        setHasToBeEvaluated(false);
      } else {
        setRetries(retries + 1);
        setHasToBeEvaluated(retries < MAXIMUM_RETRIES);
      }
    }
  }, [axesRates]);

  useEffect(() => {
    if (hasToBeEvaluated && retries >= MAXIMUM_RETRIES) {
      setHasToBeEvaluated(false);
    }
  }, [retries]);

  useEffect(() => {
    if (productId && productUpdated) {
      setRetries(0);
      setHasToBeEvaluated(true);
    }
  }, [productId, productUpdated]);

  return axesRates;
};

export default useFetchProductAxisRates;
