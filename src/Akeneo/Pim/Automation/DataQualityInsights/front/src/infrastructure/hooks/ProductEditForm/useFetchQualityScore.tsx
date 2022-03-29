import {useEffect, useState} from 'react';
import {useSelector} from 'react-redux';
import {fetchProductQualityScore} from '../../fetcher/ProductEditForm/fetchProductQualityScore';
import {fetchProductModelQualityScore} from '../../fetcher/ProductEditForm/fetchProductModelQualityScore';
import {ProductEditFormState} from '../../store';
import {QualityScoreModel} from '../../../domain';
import {ProductType} from '../../../domain/Product.interface';

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

const useFetchQualityScore = (channel: string | undefined, locale: string | undefined) => {
  const [retries, setRetries] = useState<number>(0);
  const [qualityScore, setQualityScore] = useState<QualityScoreModel | null>(null);
  const [needsUpdate, setNeedsUpdate] = useState<boolean>(true);
  const [isLoading, setIsLoading] = useState<boolean>(false);

  const {productId, productUpdatedDate, isEvaluating, productType} = useSelector((state: ProductEditFormState) => {
    return {
      productId: state.product.meta.id,
      productType: state.product.meta.model_type as ProductType,
      productUpdatedDate: state.product.updated,
      isEvaluating: state.pageContext.isProductEvaluating,
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
    if (isEvaluating && null !== productUpdatedDate) {
      setIsLoading(true);
    } else {
      setIsLoading(false);
    }
  }, [isEvaluating]);

  useEffect(() => {
    setNeedsUpdate(true);
    setRetries(0);
  }, [productUpdatedDate]);

  useEffect(() => {
    if (productId && needsUpdate) {
      loadQualityScore(productId, retries);
    }
    if (false === needsUpdate) {
      setIsLoading(false);
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

  const loadQualityScore = (productId: number, retries: number) => {
    setTimeout(() => {
      (async () => {
        const fetcher = productType === 'product' ? fetchProductQualityScore : fetchProductModelQualityScore;
        const score = await fetcher(productId);
        if (productId) {
          setQualityScore(score);
        }
      })();
    }, getRetryDelay(retries));
  };

  const score = qualityScore !== null && channel && locale ? qualityScore[channel][locale] : null;

  return {
    score,
    isLoading,
    productType,
  };
};

export {useFetchQualityScore};
