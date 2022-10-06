import React, {FC, useMemo} from 'react';
import {FetcherContext} from '../contexts';
import {TimeToEnrich} from '../../TimeToEnrich';
import {useRouter} from '@akeneo-pim-community/shared';

const PimFetcherProvider: FC = ({children}) => {
  const router = useRouter();
  const fetcherValue = useMemo(
    () => ({
      timeToEnrich: {
        fetchHistoricalTimeToEnrich: async (
          startDate: string,
          endDate: string,
          periodType: string
        ): Promise<TimeToEnrich[]> => {
          const response = await fetch(
            router.generate('pimee_performance_analytics_historical_average_tte') +
              '?' +
              new URLSearchParams({start_date: startDate, end_date: endDate, period_type: periodType}),
            {
              method: 'GET',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
            }
          );
          return await response.json();
        },
      },
    }),
    [router]
  );

  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

export {PimFetcherProvider};
