import {useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';
import {get as _get} from 'lodash';

import {getProductEvaluationAction} from '../../reducer';
import {useCatalogContext, useProductEvaluation} from '../index';
import ProductEvaluationFetcher from '../../fetcher/ProductEditForm/ProductEvaluationFetcher';

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

const useFetchProductDataQualityEvaluation = (productEvaluationFetcher: ProductEvaluationFetcher) => {
  const [hasToBeEvaluated, setHasToBeEvaluated] = useState<boolean>(false);
  const [retries, setRetries] = useState<number>(0);
  const {channel, locale} = useCatalogContext();

  const {productId, productUpdated, evaluation} = useProductEvaluation();

  const dispatchAction = useDispatch();

  useEffect(() => {
    if (productId && hasToBeEvaluated && retries < MAXIMUM_RETRIES) {
      setTimeout(() => {
        (async () => {
          if (!productId) {
            return;
          }
          const data = await productEvaluationFetcher(productId);
          dispatchAction(getProductEvaluationAction(productId, data));
        })();
      }, getRetryDelay(retries));
    }
  }, [hasToBeEvaluated, retries]);

  useEffect(() => {
    if (evaluation !== undefined) {
      const notEvaluatedAxesList = Object.values(evaluation).filter(axisEvaluation => {
        // @ts-ignore
        return _get(axisEvaluation, [channel, locale, 'rate']) === null;
      });

      if (notEvaluatedAxesList.length === 0) {
        setRetries(0);
        setHasToBeEvaluated(false);
      } else {
        setRetries(retries + 1);
        setHasToBeEvaluated(retries < MAXIMUM_RETRIES);
      }
    }
  }, [evaluation]);

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

  return evaluation;
};

export default useFetchProductDataQualityEvaluation;
