import {createContext, useContext} from 'react';
import {TimeToEnrich} from '../../TimeToEnrich';
import {Channel, ChannelCode, Family, FamilyCode, Locale, LocaleCode} from '../models';

export type FetcherValue = {
  timeToEnrich: {
    fetchHistoricalTimeToEnrich: (
      startDate: string,
      endDate: string,
      periodType: string,
      filters: {
        families: FamilyCode[];
        channels: ChannelCode[];
        locales: LocaleCode[];
      }
    ) => Promise<TimeToEnrich[]>;
    fetchAverageTimeToEnrichByEntity: (
      startDate: string,
      endDate: string,
      aggregationType: string,
      filters: {
        channels: ChannelCode[];
        locales: LocaleCode[];
      }
    ) => Promise<TimeToEnrich[]>;
  };
  family: {
    fetchFamilies: (limit: number, page: number, search?: string) => Promise<{[key: string]: Family}>;
    fetchAllFamilies: () => Promise<{[key: string]: Family}>;
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
    fetchAverageTimeToEnrichByEntity: () => {
      throw new Error('Fetch attributes by identifiers needs to be implemented');
    },
  },
  family: {
    fetchFamilies: () => {
      throw new Error('Fetch families needs to be implemented');
    },
    fetchAllFamilies: () => {
      throw new Error('Fetch all families needs to be implemented');
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
