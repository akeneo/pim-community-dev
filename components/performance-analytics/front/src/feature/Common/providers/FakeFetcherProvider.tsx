import React, {FC, useMemo} from 'react';
import {FetcherContext} from '../contexts';
import {TimeToEnrich} from '../../TimeToEnrich';
import moment from 'moment';
import {Channel, ChannelCode, Family, FamilyCode, Locale, LocaleCode} from '../models';

const families: Family[] = Array.from(Array(100).keys()).map((index: number) => {
  return {
    code: `family_${index}`,
    labels: {
      en_US: `Family ${index}`,
    },
  };
});

const timeToEnrichByEntity = [
  {
    code: 'family_1',
    value: 44,
  },
  {
    code: 'family_2',
    value: 10,
  },
  {
    code: 'family_3',
    value: 41,
  },
  {
    code: 'family_4',
    value: 100,
  },
];

const channels: Channel[] = [
  {
    code: 'ecommerce',
    labels: {
      en_US: 'Ecommerce',
    },
  },
  {
    code: 'mobile',
    labels: {
      en_US: 'Mobile',
    },
  },
  {
    code: 'print',
    labels: {
      en_US: 'Print',
    },
  },
];

const locales: Locale[] = [
  {
    code: 'en_US',
    label: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French',
  },
  {
    code: 'de_DE',
    label: 'German',
  },
];

const FakeFetcherProvider: FC = ({children}) => {
  const fetcherValue = useMemo(
    () => ({
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
        ): Promise<TimeToEnrich[]> => {
          const timeToEnrichList: TimeToEnrich[] = [];
          let cursorDate = moment(startDate);
          const end = moment(endDate);

          while (cursorDate <= end) {
            timeToEnrichList.push({
              code: cursorDate.format('YYYY-[W]WW'),
              value: Math.floor(Math.random() * 100),
            });
            cursorDate.add(1, 'w');
          }

          return new Promise(resolve => resolve(timeToEnrichList));
        },
        fetchAverageTimeToEnrichByEntity: (
          startDate: string,
          endDate: string,
          aggregationType: string,
          filters: {
            channels: ChannelCode[];
            locales: LocaleCode[];
          }
        ): Promise<TimeToEnrich[]> => {
          return new Promise(resolve => resolve(timeToEnrichByEntity));
        },
      },
      family: {
        fetchFamilies: (limit: number, page: number, search?: string): Promise<{[key: string]: Family}> => {
          const filterFamilies = search
            ? families.filter((family: Family): boolean => family.code.indexOf(search) > 0)
            : families;
          return new Promise(resolve => {
            const paginatedFamilies = filterFamilies.slice((page - 1) * limit, page * limit);
            resolve(
              paginatedFamilies.reduce((map, family: Family) => {
                map[family.code] = family;
                return map;
              }, {})
            );
          });
        },
        fetchAllFamilies: (): Promise<{[key: string]: Family}> => {
          return new Promise(resolve =>
            resolve(
              families.reduce((map, family: Family) => {
                map[family.code] = family;
                return map;
              }, {})
            )
          );
        },
      },
      channel: {
        fetchChannels: async (): Promise<Channel[]> => {
          return new Promise(resolve => resolve(channels));
        },
      },
      locale: {
        fetchActivatedLocales: async (): Promise<Locale[]> => {
          return new Promise(resolve => resolve(locales));
        },
      },
    }),
    []
  );
  return <FetcherContext.Provider value={fetcherValue}>{children}</FetcherContext.Provider>;
};

export {FakeFetcherProvider};
