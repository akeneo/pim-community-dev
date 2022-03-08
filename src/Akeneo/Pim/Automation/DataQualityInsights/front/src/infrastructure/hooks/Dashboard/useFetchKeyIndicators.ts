import {useEffect, useState} from 'react';
import {fetchKeyIndicators} from '../../fetcher';
import {keyIndicatorMap} from '../../../domain';

const useFetchKeyIndicators = (
  channel: string,
  locale: string,
  familyCode: string | null,
  categoryCode: string | null
) => {
  const [keyIndicators, setKeyIndicators] = useState<keyIndicatorMap | null>(null);

  useEffect(() => {
    setKeyIndicators(null);
  }, [channel, locale, familyCode, categoryCode]);

  useEffect(() => {
    (async () => {
      //const data = await fetchKeyIndicators(channel, locale, familyCode, categoryCode);
      const data = {
        good_enrichment: {
          products: {
            totalGood: 16,
            totalToImprove: 1021,
          },
          product_models: {
            totalGood: 16,
            totalToImprove: 1021,
          },
        },
        has_image: {
          products: {
            totalGood: 219,
            totalToImprove: 1045,
          },
          product_models: {
            totalGood: 219,
            totalToImprove: 1045,
          },
        },
      };
      setKeyIndicators(data);
    })();
  }, [channel, locale, familyCode, categoryCode]);

  return keyIndicators;
};

export {useFetchKeyIndicators};
