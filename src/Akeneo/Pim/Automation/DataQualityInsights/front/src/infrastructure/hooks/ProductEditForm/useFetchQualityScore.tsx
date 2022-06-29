import {useEffect, useRef, useState} from 'react';
import {fetchQualityScore, Payload as FetchQualityscorePlayload} from '../../fetcher/ProductEditForm/fetchQualityScore';
import {QualityScoreModel} from '../../../domain';
import {ProductType} from '../../../domain/Product.interface';

const MAX_NB_ATTEMPTS = 10;
const RETRY_MILLISECONDS_DELAY = 500;

type InnerFetcherOutcome =
  | {
      status: 'loading';
    }
  | {
      status: 'loaded';
      scores: QualityScoreModel;
    }
  | {
      status: 'failed';
      error: string;
    };

export type QualityScoresFetchingOutcome =
  | {
      status: 'init';
    }
  | InnerFetcherOutcome
  | {
      status: 'attempts exhausted';
    };

const initialOutcome: QualityScoresFetchingOutcome = {
  status: 'init',
};

export type RetryDelayCalculator = (retry: number) => number;

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
const defaultGetRetryDelay: RetryDelayCalculator = retry => retry * retry * RETRY_MILLISECONDS_DELAY;

export const sleep = (delay: number) => new Promise(resolve => setTimeout(resolve, delay));

async function loadQualityScore(type: ProductType, id: string): Promise<InnerFetcherOutcome> {
  let fetchedPayload: FetchQualityscorePlayload = {evaluations_available: false};
  try {
    fetchedPayload = await fetchQualityScore(type, id);
  } catch (e) {
    return {status: 'failed', error: e.message};
  }

  if (fetchedPayload.evaluations_available) {
    return {status: 'loaded', scores: fetchedPayload.scores};
  }

  return {status: 'loading'};
}

type HooksReturn = {
  outcome: QualityScoresFetchingOutcome;
  fetcher: () => Promise<void>;
};

const useFetchQualityScore = (
  type: ProductType,
  id: string | null,
  getRetryDelay: RetryDelayCalculator = defaultGetRetryDelay
): HooksReturn => {
  const [scoresFetchingOutcome, setScoresFetchingOutcome] = useState<QualityScoresFetchingOutcome>(initialOutcome);

  const abort = useRef(false);

  useEffect(() => {
    // cleanup if the component is unmounted while loading : we must cancel the retry loop
    return () => {
      abort.current = true;
    };
  }, []);

  useEffect(() => {
    setScoresFetchingOutcome({status: 'init'});
    abort.current = false;
  }, [id, type]);

  if (id === null) {
    // dev error
    return {
      outcome: {
        status: 'failed',
        error: 'entity has null id',
      },
      fetcher: () => Promise.resolve(),
    };
  }

  return {
    outcome: scoresFetchingOutcome,
    fetcher: async () => {
      setScoresFetchingOutcome({status: 'loading'});
      let remaingAttempts = MAX_NB_ATTEMPTS;
      while (remaingAttempts--) {
        await sleep(getRetryDelay(MAX_NB_ATTEMPTS - remaingAttempts));
        if (abort.current) {
          // component unmounted
          return;
        }
        const outcome = await loadQualityScore(type, id);
        if (abort.current) {
          // component unmounted
          return;
        }
        switch (outcome.status) {
          case 'failed': // no break
          case 'loaded': {
            setScoresFetchingOutcome(outcome);
            return;
          }
        }
      }
      setScoresFetchingOutcome({status: 'attempts exhausted'});
    },
  };
};

export {useFetchQualityScore};
