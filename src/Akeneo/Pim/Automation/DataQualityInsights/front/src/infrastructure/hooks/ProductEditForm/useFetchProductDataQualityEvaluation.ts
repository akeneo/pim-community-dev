import {useEffect, useState} from 'react';
import {useDispatch} from 'react-redux';

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

  // @todo To rework to avoid the "@ts-ignore"
  const hasEvaluationInProgress = function (): boolean {
    // @ts-ignore
    if (false === evaluation.hasOwnProperty(channel)) {
      return false;
    }

    // @ts-ignore
    const channelEvaluations = evaluation[channel];
    if (false === channelEvaluations.hasOwnProperty(locale)) {
      return false;
    }

    // @ts-ignore
    for (const criterionEvaluation of channelEvaluations[locale]) {
      if (criterionEvaluation.status === 'in_progress') {
        return true;
      }
    }

    return false;
  };

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
      if (true === hasEvaluationInProgress()) {
        setRetries(retries + 1);
        setHasToBeEvaluated(retries < MAXIMUM_RETRIES);
      } else {
        setRetries(0);
        setHasToBeEvaluated(false);
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
