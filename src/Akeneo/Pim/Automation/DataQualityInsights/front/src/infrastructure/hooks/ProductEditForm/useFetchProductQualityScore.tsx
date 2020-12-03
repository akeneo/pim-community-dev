import {useEffect, useState} from 'react';
import {useSelector} from 'react-redux';
import {fetchProductQualityScore} from '../../fetcher/ProductEditForm/fetchProductQualityScore';
import {ProductEditFormState} from '../../store';
import {ProductQualityScore} from '../../../domain';

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

const useFetchProductQualityScore = (channel: string | undefined, locale: string | undefined) => {
  const [retries, setRetries] = useState<number>(0);
  const [qualityScore, setQualityScore] = useState<ProductQualityScore | null>(null);
  const [needsUpdate, setNeedsUpdate] = useState<boolean>(true);

  const {productId, productUpdated} = useSelector((state: ProductEditFormState) => {
    return {
      productId: state.product.meta.id,
      productUpdated: state.product.updated,
    };
  });

  useEffect(() => {
    return () => {
      setNeedsUpdate(false);
      setRetries(0);
      setQualityScore(null);
    };
  }, []);

  useEffect(() => {
    setNeedsUpdate(true);
    setRetries(0);
  }, [productUpdated]);

  useEffect(() => {
    if (productId && needsUpdate) {
      loadProductQualityScore(productId, retries);
    }
  }, [productId, retries, needsUpdate]);

  useEffect(() => {
    if (productId && qualityScore !== null) {
      if (qualityScore[channel as string][locale as string] !== null) {
        setRetries(0);
        setNeedsUpdate(false);
      } else {
        if (retries < MAXIMUM_RETRIES) {
          setNeedsUpdate(true);
          setRetries(retries + 1);
        } else {
          setNeedsUpdate(false);
        }
      }
    }
  }, [qualityScore, productId]);

  const loadProductQualityScore = (productId: number, retries: number) => {
    setTimeout(() => {
      (async () => {
        const score = await fetchProductQualityScore(productId);
        if (productId) {
          setQualityScore(score);
        }
      })();
    }, getRetryDelay(retries));
  };

  return qualityScore !== null && channel && locale ? qualityScore[channel][locale] : null;
};

export {useFetchProductQualityScore};
