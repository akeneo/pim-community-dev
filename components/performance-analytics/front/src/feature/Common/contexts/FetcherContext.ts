import {createContext, useContext} from 'react';
import {TimeToEnrich} from '../../TimeToEnrich';

type FetcherValue = {
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: (startDate: string, endDate: string, periodType: string) => Promise<TimeToEnrich[]>;
  };
};

const FetcherContext = createContext<FetcherValue>({
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: () => {
      throw new Error('Fetch attributes by identifiers needs to be implemented');
    },
  },
});

const useFetchers = (): FetcherValue => {
  const fetchers = useContext(FetcherContext);

  return fetchers;
};

export {FetcherContext, useFetchers};
