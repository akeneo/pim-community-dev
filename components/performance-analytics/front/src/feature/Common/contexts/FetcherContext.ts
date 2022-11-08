import {createContext, useContext} from 'react';
import {TimeToEnrich} from '../../TimeToEnrich';
import {Channel, Family, Locale} from '../models';

type FetcherValue = {
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: (startDate: string, endDate: string, periodType: string) => Promise<TimeToEnrich[]>;
  };
  family: {
    fetchFamilies: (limit: number, page: number, search?: string) => Promise<{[key: string]: Family}>;
  };
  channel: {
    fetchChannels: () => Promise<Channel[]>;
  };
  locale: {
    fetchActivatedLocales: () => Promise<Locale[]>;
  };
};

const FetcherContext = createContext<FetcherValue>({
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: () => {
      throw new Error('Fetch attributes by identifiers needs to be implemented');
    },
  },
  family: {
    fetchFamilies: () => {
      throw new Error('Fetch families needs to be implemented');
    },
  },
  channel: {
    fetchChannels: () => {
      throw new Error('Fetch channels needs to be implemented');
    },
  },
  locale: {
    fetchActivatedLocales: () => {
      throw new Error('Fetch locales needs to be implemented');
    },
  },
});

const useFetchers = (): FetcherValue => {
  const fetchers = useContext(FetcherContext);

  return fetchers;
};

export {FetcherContext, useFetchers};
