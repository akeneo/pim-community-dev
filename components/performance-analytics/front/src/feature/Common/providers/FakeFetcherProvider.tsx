import React, {FC, useMemo} from 'react';
import {FetcherContext} from '../contexts';
import {TimeToEnrich} from '../../TimeToEnrich';
import moment from 'moment';

const FakeFetcherProvider: FC = ({children}) => {
  const fetcherValue = useMemo(
    () => ({
      timeToEnrich: {
        fetchHistoricalTimeToEnrich: (
          startDate: string,
          endDate: string,
          periodType: string
        ): Promise<TimeToEnrich[]> => {
          const timeToEnrichList: TimeToEnrich[] = [];
          let cursorDate = moment(startDate);
          const end = moment(endDate);

          while (cursorDate <= end) {
            timeToEnrichList.push({
              period: cursorDate.format('YYYY-[W]WW'),
              value: Math.floor(Math.random() * 100),
            });
            cursorDate.add(1, 'w');
          }

          return new Promise(resolve => resolve(timeToEnrichList));
        },
      },
    }),
    []
  );
  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

export {FakeFetcherProvider};
